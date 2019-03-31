<div class="virgil-phe-global-container">

    <?php
    use Plugin\Pure\Core\PageBuilder;
    require_once 'blocks/header.php';

    $pb = new PageBuilder();

    if ($pb->disabledBlock())
        require_once 'blocks/disabled.php';

    if ($pb->demoModeBlock())
        require_once 'blocks/demo.php';

    if ($pb->credentialsBlock())
        require_once 'blocks/credentials.php';

    if ($pb->migrateBlock())
        require_once 'blocks/migrate.php';

    if ($pb->updateBlock())
        require_once 'blocks/update.php';

    if ($pb->infoBlock())
        require_once 'blocks/info.php';

    if ($pb->logBlock())
        require_once 'blocks/log.php';

    if ($pb->faqBlock())
        require_once 'blocks/faq.php';
    ?>

</div>

