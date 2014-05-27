<?php
/**
 * Novius Blocks
 *
 * @copyright  2014 Novius
 * @license    GNU Affero General Public License v3 or (at your option) any later version
 *             http://www.gnu.org/licenses/agpl-3.0.html
 * @link http://www.novius-os.org
 */

if (!count($blocks)) {
    echo __('No block will be displayed');
    exit();
}

$templates_config = \Config::load('novius_blocks::templates', true);

?>
<div>
    <style type="text/css">
    .blocks_wrapper .block_wrapper {
        margin: 20px 0 0 0;
    }
    </style>
    <div class="blocks_wrapper blocks_wrapper_enhancer">
        <?php
        foreach ($blocks as $block) {
            $name = $block->block_template;
            if (!$template_config = $templates_config[$name]) {
                continue;
            }

            // Custom block stylesheet
            $config = \Novius\Blocks\Model_Block::init_config($template_config, $name);
            if ($config['css']) {
                ?>
                <link rel="stylesheet" href="<?= $config['css'] ?>" />
                <?php
            }

            // Does a special admin CSS file exists ?
            if (is_file(DOCROOT . 'static/css/blocks/admin/' . $name . '.preview.css')) {
                ?>
                <link rel="stylesheet" href="static/css/blocks/admin/<?= $name ?>.preview.css" />
                <?php
            } else if (is_file(DOCROOT . 'static/css/blocks/admin/' . $name . '.css')) {
                ?>
                <link rel="stylesheet" href="static/css/blocks/admin/<?= $name ?>.css" />
                <?php
            }

            // Display the block
            echo \Novius\Blocks\Controller_Front_Block::get_block_view($block, $config, $name);
        }
        ?>
    </div>
</div>
