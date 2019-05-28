<?php
use VirgilSecurityPure\Core\PageBuilderPublic;
?>

<div class="virgil-pure-global-container">
    <?php
    require_once 'blocks/_header.php';

    $pageBuilderPublic = new PageBuilderPublic();

    foreach (get_class_methods($pageBuilderPublic) as $methodBlock) {
        if($pageBuilderPublic->$methodBlock())
            require_once "blocks/$methodBlock.php";
    }
    ?>
</div>

