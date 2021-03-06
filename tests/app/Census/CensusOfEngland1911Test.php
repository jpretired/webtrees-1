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

namespace Fisharebest\Webtrees\Census;

use Fisharebest\Webtrees\TestCase;

/**
 * Test harness for the class CensusOfEngland1911
 */
class CensusOfEngland1911Test extends TestCase
{
    /**
     * Test the census place and date
     *
     * @covers \Fisharebest\Webtrees\Census\CensusOfEngland1911
     *
     * @return void
     */
    public function testPlaceAndDate(): void
    {
        $census = new CensusOfEngland1911();

        $this->assertSame('England', $census->censusPlace());
        $this->assertSame('02 APR 1911', $census->censusDate());
    }

    /**
     * Test the census columns
     *
     * @covers \Fisharebest\Webtrees\Census\CensusOfEngland1911
     * @covers \Fisharebest\Webtrees\Census\AbstractCensusColumn
     *
     * @return void
     */
    public function testColumns(): void
    {
        $census  = new CensusOfEngland1911();
        $columns = $census->columns();

        $this->assertCount(16, $columns);
        $this->assertInstanceOf(CensusColumnFullName::class, $columns[0]);
        $this->assertInstanceOf(CensusColumnRelationToHead::class, $columns[1]);
        $this->assertInstanceOf(CensusColumnAgeMale::class, $columns[2]);
        $this->assertInstanceOf(CensusColumnAgeFemale::class, $columns[3]);
        $this->assertInstanceOf(CensusColumnConditionEnglish::class, $columns[4]);
        $this->assertInstanceOf(CensusColumnYearsMarried::class, $columns[5]);
        $this->assertInstanceOf(CensusColumnChildrenBornAlive::class, $columns[6]);
        $this->assertInstanceOf(CensusColumnChildrenLiving::class, $columns[7]);
        $this->assertInstanceOf(CensusColumnChildrenDied::class, $columns[8]);
        $this->assertInstanceOf(CensusColumnOccupation::class, $columns[9]);
        $this->assertInstanceOf(CensusColumnNull::class, $columns[10]);
        $this->assertInstanceOf(CensusColumnNull::class, $columns[11]);
        $this->assertInstanceOf(CensusColumnNull::class, $columns[12]);
        $this->assertInstanceOf(CensusColumnBirthPlace::class, $columns[13]);
        $this->assertInstanceOf(CensusColumnNationality::class, $columns[14]);
        $this->assertInstanceOf(CensusColumnNull::class, $columns[15]);

        $this->assertSame('Name', $columns[0]->abbreviation());
        $this->assertSame('Relation', $columns[1]->abbreviation());
        $this->assertSame('AgeM', $columns[2]->abbreviation());
        $this->assertSame('AgeF', $columns[3]->abbreviation());
        $this->assertSame('Condition', $columns[4]->abbreviation());
        $this->assertSame('YrM', $columns[5]->abbreviation());
        $this->assertSame('ChA', $columns[6]->abbreviation());
        $this->assertSame('ChL', $columns[7]->abbreviation());
        $this->assertSame('ChD', $columns[8]->abbreviation());
        $this->assertSame('Occupation', $columns[9]->abbreviation());
        $this->assertSame('Ind', $columns[10]->abbreviation());
        $this->assertSame('Emp', $columns[11]->abbreviation());
        $this->assertSame('Home', $columns[12]->abbreviation());
        $this->assertSame('Birthplace', $columns[13]->abbreviation());
        $this->assertSame('Nat', $columns[14]->abbreviation());
        $this->assertSame('Infirm', $columns[15]->abbreviation());

        $this->assertSame('Name and surname', $columns[0]->title());
        $this->assertSame('Relation to head of household', $columns[1]->title());
        $this->assertSame('Age (males)', $columns[2]->title());
        $this->assertSame('Age (females)', $columns[3]->title());
        $this->assertSame('Condition', $columns[4]->title());
        $this->assertSame('Years married', $columns[5]->title());
        $this->assertSame('Children born alive', $columns[6]->title());
        $this->assertSame('Children who are still alive', $columns[7]->title());
        $this->assertSame('Children who have died', $columns[8]->title());
        $this->assertSame('Rank, profession or occupation', $columns[9]->title());
        $this->assertSame('Industry', $columns[10]->title());
        $this->assertSame('Employer, worker or own account', $columns[11]->title());
        $this->assertSame('Working at home', $columns[12]->title());
        $this->assertSame('Where born', $columns[13]->title());
        $this->assertSame('Nationality', $columns[14]->title());
        $this->assertSame('Infirmity', $columns[15]->title());
    }
}
