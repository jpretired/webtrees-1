<?php use Fisharebest\Webtrees\Bootstrap4; ?>
<?php use Fisharebest\Webtrees\FontAwesome; ?>
<?php use Fisharebest\Webtrees\Functions\FunctionsEdit; ?>
<?php use Fisharebest\Webtrees\GedcomCode\GedcomCodePedi; ?>
<?php use Fisharebest\Webtrees\I18N; ?>

<h2 class="wt-page-title"><?= $title ?></h2>

<form class="wt-page-content" method="post">
    <?= csrf_field() ?>

    <div class="row form-group">
        <label class="col-sm-3 col-form-label" for="famid">
            <?= I18N::translate('Family') ?>
        </label>
        <div class="col-sm-9">
            <?= FunctionsEdit::formControlFamily($tree, null, ['id' => 'famid', 'name' => 'famid']) ?>
        </div>
    </div>

    <div class="row form-group">
        <label class="col-sm-3 col-form-label" for="PEDI">
            <?= I18N::translate('Pedigree') ?>
        </label>
        <div class="col-sm-9">
            <?= Bootstrap4::select(GedcomCodePedi::getValues($individual), '', ['id' => 'PEDI', 'name' => 'PEDI']) ?>
            <p class="small text-muted">
                <?= I18N::translate('A child may have more than one set of parents. The relationship between the child and the parents can be biological, legal, or based on local culture and tradition. If no pedigree is specified, then a biological relationship will be assumed.') ?>
            </p>
        </div>
    </div>

    <div class="row form-group">
        <div class="col-sm-9 offset-sm-3">
            <button class="btn btn-primary" type="submit">
                <?= FontAwesome::decorativeIcon('save') ?>
                <?= /* I18N: A button label. */
                I18N::translate('save') ?>
            </button>
            <a class="btn btn-secondary" href="<?= e($individual->url()) ?>">
                <?= FontAwesome::decorativeIcon('cancel') ?>
                <?= /* I18N: A button label. */
                I18N::translate('cancel') ?>
            </a>
        </div>
    </div>
</form>