<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arResult
 */

if (!$arResult['REVIEWS']) {
    return;
}
?>
<div class="container mt-5">
    <div class="row">
        <?php
        foreach ($arResult['REVIEWS'] as $review) : ?>
            <div class="col-md-12 mb-4">
                <div class="card">
                    <img src="<?= $review['PREVIEW_PICTURE']['SRC'] ?>" class="card-img-top"
                         alt="<?= $review['PREVIEW_PICTURE']['ALT'] ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= $review['PROPERTY_NAME_VALUE'] ?></h5>
                        <p class="card-text"><?= $review['PREVIEW_TEXT'] ?></p>
                    </div>
                </div>
            </div>
        <?php
        endforeach; ?>
    </div>
</div>