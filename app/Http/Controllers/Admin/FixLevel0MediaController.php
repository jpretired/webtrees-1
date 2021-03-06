<?php

/**
 * webtrees: online genealogy
 * Copyright (C) 2019 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Fisharebest\Webtrees\Http\Controllers\Admin;

use Fisharebest\Webtrees\Fact;
use Fisharebest\Webtrees\Gedcom;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Media;
use Fisharebest\Webtrees\Services\DatatablesService;
use Fisharebest\Webtrees\Services\TreeService;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Controller for fixing media links.
 */
class FixLevel0MediaController extends AbstractAdminController
{
    /** @var DatatablesService */
    private $datatables_service;

    /** @var TreeService */
    private $tree_service;

    /**
     * FixLevel0MediaController constructor.
     *
     * @param DatatablesService $datatables_service
     * @param TreeService       $tree_service
     */
    public function __construct(DatatablesService $datatables_service, TreeService $tree_service)
    {
        $this->datatables_service = $datatables_service;
        $this->tree_service       = $tree_service;
    }

    /**
     * If media objects are wronly linked to top-level records, reattach them
     * to facts/events.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function fixLevel0Media(ServerRequestInterface $request): ResponseInterface
    {
        return $this->viewResponse('admin/fix-level-0-media', [
            'title' => I18N::translate('Link media objects to facts and events'),
        ]);
    }

    /**
     * Move a link to a media object from a level 0 record to a level 1 record.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function fixLevel0MediaAction(ServerRequestInterface $request): ResponseInterface
    {
        $params = (array) $request->getParsedBody();

        $fact_id   = $params['fact_id'];
        $indi_xref = $params['indi_xref'];
        $obje_xref = $params['obje_xref'];
        $tree_id   = (int) $params['tree_id'];

        $tree       = $this->tree_service->find($tree_id);
        $individual = Individual::getInstance($indi_xref, $tree);
        $media      = Media::getInstance($obje_xref, $tree);

        if ($individual !== null && $media !== null) {
            foreach ($individual->facts() as $fact1) {
                if ($fact1->id() === $fact_id) {
                    $individual->updateFact($fact_id, $fact1->gedcom() . "\n2 OBJE @" . $obje_xref . '@', false);
                    foreach ($individual->facts(['OBJE']) as $fact2) {
                        if ($fact2->target() === $media) {
                            $individual->deleteFact($fact2->id(), false);
                        }
                    }
                    break;
                }
            }
        }

        return response();
    }

    /**
     * If media objects are wronly linked to top-level records, reattach them
     * to facts/events.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function fixLevel0MediaData(ServerRequestInterface $request): ResponseInterface
    {
        $ignore_facts = [
            'NAME',
            'SEX',
            'CHAN',
            'NOTE',
            'SOUR',
            'RESN',
        ];

        $prefix = DB::connection()->getTablePrefix();

        $query = DB::table('media')
            ->join('media_file', static function (JoinClause $join): void {
                $join
                    ->on('media_file.m_file', '=', 'media.m_file')
                    ->on('media_file.m_id', '=', 'media.m_id');
            })
            ->join('link', static function (JoinClause $join): void {
                $join
                    ->on('link.l_file', '=', 'media.m_file')
                    ->on('link.l_to', '=', 'media.m_id')
                    ->where('link.l_type', '=', 'OBJE');
            })
            ->join('individuals', static function (JoinClause $join): void {
                $join
                    ->on('individuals.i_file', '=', 'link.l_file')
                    ->on('individuals.i_id', '=', 'link.l_from');
            })
            ->where('i_gedcom', 'LIKE', new Expression("('%\n1 OBJE @' || " . $prefix . "media.m_id || '@%')"))
            ->orderBy('individuals.i_file')
            ->orderBy('individuals.i_id')
            ->orderBy('media.m_id')
            ->whereContains('descriptive_title', $request->getQueryParams()['search']['value'] ?? '')
            ->select(['media.m_file', 'media.m_id', 'media.m_gedcom', 'individuals.i_id', 'individuals.i_gedcom']);

        return $this->datatables_service->handleQuery($request, $query, [], [], function (stdClass $datum) use ($ignore_facts): array {
            $tree       = $this->tree_service->find((int) $datum->m_file);
            $media      = Media::getInstance($datum->m_id, $tree, $datum->m_gedcom);
            $individual = Individual::getInstance($datum->i_id, $tree, $datum->i_gedcom);

            $facts = $individual->facts([], true)
                ->filter(static function (Fact $fact) use ($ignore_facts): bool {
                    return
                        !$fact->isPendingDeletion() &&
                        !preg_match('/^@' . Gedcom::REGEX_XREF . '@$/', $fact->value()) &&
                        !in_array($fact->getTag(), $ignore_facts, true);
                });

            // The link to the media object may have been deleted in a pending change.
            $deleted = true;
            foreach ($individual->facts(['OBJE']) as $fact) {
                if ($fact->target() === $media && !$fact->isPendingDeletion()) {
                    $deleted = false;
                }
            }
            if ($deleted) {
                $facts = new Collection();
            }

            $facts = $facts->map(static function (Fact $fact) use ($individual, $media): string {
                return view('admin/fix-level-0-media-action', [
                    'fact'       => $fact,
                    'individual' => $individual,
                    'media'      => $media,
                ]);
            });

            return [
                $tree->name(),
                $media->displayImage(100, 100, 'contain', ['class' => 'img-thumbnail']),
                '<a href="' . e($media->url()) . '">' . $media->fullName() . '</a>',
                '<a href="' . e($individual->url()) . '">' . $individual->fullName() . '</a>',
                $facts->implode(' '),
            ];
        });
    }
}
