<?php

use CodeIgniter\Pager\PagerRenderer;

/** @var PagerRenderer $pager */

$pager->setSurroundCount(2);
$previousUrl = $pager->hasPreviousPage() ? $pager->getPreviousPage() : null;
$nextUrl = $pager->hasNextPage() ? $pager->getNextPage() : null;
?>

<nav aria-label="Navigasi halaman">
    <ul class="pagination pagination-sm align-items-center mb-0">
        <li class="page-item <?= $pager->getCurrentPageNumber() === 1 ? 'disabled' : '' ?>">
            <?php if ($pager->getCurrentPageNumber() === 1) : ?>
                <span class="page-link" aria-disabled="true" title="Halaman pertama"><i class="bi bi-chevron-double-left" aria-hidden="true"></i></span>
            <?php else : ?>
                <a class="page-link" href="<?= $pager->getFirst() ?>" aria-label="Halaman pertama" title="Halaman pertama"><i class="bi bi-chevron-double-left" aria-hidden="true"></i></a>
            <?php endif; ?>
        </li>
        <li class="page-item <?= $previousUrl === null ? 'disabled' : '' ?>">
            <?php if ($previousUrl === null) : ?>
                <span class="page-link" aria-disabled="true" title="Halaman sebelumnya"><i class="bi bi-chevron-left" aria-hidden="true"></i></span>
            <?php else : ?>
                <a class="page-link" href="<?= $previousUrl ?>" aria-label="Halaman sebelumnya" title="Halaman sebelumnya"><i class="bi bi-chevron-left" aria-hidden="true"></i></a>
            <?php endif; ?>
        </li>

        <?php foreach ($pager->links() as $link) : ?>
            <li class="page-item <?= $link['active'] ? 'active' : '' ?>"><a class="page-link" href="<?= $link['uri'] ?>" <?= $link['active'] ? 'aria-current="page"' : '' ?>><?= $link['title'] ?></a></li>
        <?php endforeach; ?>

        <li class="page-item <?= $nextUrl === null ? 'disabled' : '' ?>">
            <?php if ($nextUrl === null) : ?>
                <span class="page-link" aria-disabled="true" title="Halaman berikutnya"><i class="bi bi-chevron-right" aria-hidden="true"></i></span>
            <?php else : ?>
                <a class="page-link" href="<?= $nextUrl ?>" aria-label="Halaman berikutnya" title="Halaman berikutnya"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
            <?php endif; ?>
        </li>
        <li class="page-item <?= $pager->getCurrentPageNumber() === $pager->getPageCount() ? 'disabled' : '' ?>">
            <?php if ($pager->getCurrentPageNumber() === $pager->getPageCount()) : ?>
                <span class="page-link" aria-disabled="true" title="Halaman terakhir"><i class="bi bi-chevron-double-right" aria-hidden="true"></i></span>
            <?php else : ?>
                <a class="page-link" href="<?= $pager->getLast() ?>" aria-label="Halaman terakhir" title="Halaman terakhir"><i class="bi bi-chevron-double-right" aria-hidden="true"></i></a>
            <?php endif; ?>
        </li>
    </ul>
</nav>