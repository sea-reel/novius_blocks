<?php
/**
 * Novius Blocks
 *
 * @copyright  2014 Novius
 * @license    GNU Affero General Public License v3 or (at your option) any later version
 *             http://www.gnu.org/licenses/agpl-3.0.html
 * @link       http://www.novius-os.org
 */

namespace Novius\Blocks;

/**
 * Class Model_Block
 *
 * @property int    block_id
 * @property string block_title
 * @property string block_template
 * @property string block_link_title
 * @property bool   block_link_new_page
 * @property string block_class
 * @property string block_model
 * @property int    block_model_id
 * @package Novius\Blocks
 */
class Model_Block extends \Nos\Orm\Model
{

    protected static $_primary_key = array('block_id');
    protected static $_table_name = 'novius_blocks';

    protected static $_properties = array(
        'block_id'            => array(
            'default'   => null,
            'data_type' => 'int unsigned',
            'null'      => false,
        ),
        'block_title'         => array(
            'default'   => null,
            'data_type' => 'varchar',
            'null'      => false,
        ),
        'block_template'      => array(
            'default'   => null,
            'data_type' => 'varchar',
            'null'      => true,
        ),
        'block_link_title'    => array(
            'default'   => null,
            'data_type' => 'varchar',
            'null'      => true,
        ),
        'block_link_new_page' => array(
            'default'   => null,
            'data_type' => 'boolean',
            'null'      => true,
        ),
        'block_class'         => array(
            'default'   => null,
            'data_type' => 'varchar',
            'null'      => true,
        ),
        'block_model'         => array(
            'default'   => null,
            'data_type' => 'varchar',
            'null'      => true,
        ),
        'block_model_id'      => array(
            'default'   => null,
            'data_type' => 'int unsigned',
            'null'      => true,
        ),
        'block_hidden'        => array(
            'default'   => 0,
            'data_type' => 'boolean',
            'null'      => false,
        ),
    );

    protected static $_observers = array(
        'Orm\Observer_CreatedAt' => array(
            'events'          => array('before_insert'),
            'mysql_timestamp' => true,
            'property'        => 'block_created_at'
        ),
        'Orm\Observer_UpdatedAt' => array(
            'events'          => array('before_save'),
            'mysql_timestamp' => true,
            'property'        => 'block_updated_at'
        ),
        'Orm\Observer_Self'      => array(),
    );

    protected static $_behaviours = array(
        'Nos\Orm_Behaviour_Urlenhancer' => array(
            'enhancers' => array('blocks_block'),
        ),
        'Nos\Orm_Behaviour_Contextable' => array(
            'events'           => array('before_insert'),
            'context_property' => 'block_context',
        ),
        'Nos\Orm_Behaviour_Twinnable'   => array(
            'events'             => array('before_insert', 'after_insert', 'before_save', 'after_delete', 'change_parent'),
            'context_property'   => 'block_context',
            'common_id_property' => 'block_context_common_id',
            'is_main_property'   => 'block_context_is_main',
            'common_fields'      => array(),
        ),
    );

    protected static $_many_many = array(
        'columns' => array(
            'table_through'    => 'novius_blocks_columns_liaison',
            'key_from'         => 'block_id',
            'key_through_from' => 'blcl_block_id',
            'key_through_to'   => 'blcl_blco_id',
            'key_to'           => 'blco_id',
            'cascade_save'     => false,
            'cascade_delete'   => false,
            'model_to'         => 'Novius\Blocks\Model_Column',
        ),
    );

    protected static $_has_many = array(
        'attributes' => array(
            'key_from'       => 'block_id', // key in this model
            'model_to'       => 'Novius\Blocks\Model_Block_Attribute',
            'key_to'         => 'blat_block_id', // key in the related model
            'cascade_save'   => true, // update the related table on save
            'cascade_delete' => true, // delete the related data when deleting the parent
        ),
    );

    protected static $_eav = array(
        'attributes' => array( // we use the statistics relation to store the EAV data
            'attribute' => 'blat_key', // the key column in the related table contains the attribute
            'value'     => 'blat_value', // the value column in the related table contains the value
        )
    );

    /**
     * Return the link that goes with the block
     *
     * @return mixed|\Nos\Orm\Model|null
     */
    public function url()
    {
        if ($this->block_model_id && $this->block_model) {

            $model = $this->block_model;
            $item  = $model::find($this->block_model_id);
            if (!empty($item)) {
                try {
                    return $item->url();
                } catch (\Exception $e) {
                    return null;
                }
            }
        }
        return $this->block_link;
    }

    public function getConfig()
    {
        return static::config($this->block_template);
    }

    public static function config($type)
    {
        $configBloc    = \Config::load("novius_blocks::block/$type", true);
        $configDefault = \Config::load("novius_blocks::block/default", true);
        $blockConfig   = \Arr::merge($configDefault, $configBloc);
        $list          = array('view', 'preview');
        foreach ($list as $property) {
            if (isset($blockConfig[$property])) {
                $blockConfig[$property] = strtr($blockConfig[$property], array('{name}' => $type));
            }
        }
        return $blockConfig;
    }

    /**
     * If a block is not set in the order field of its columns, it is set in the last position.
     */
    public function _event_after_save()
    {
        foreach ($this->columns as $column) {
            if (!empty($column->blco_blocks_ordre)) {
                $blocks_order = (array)unserialize($column->blco_blocks_ordre);
            } else {
                $blocks_order = array();
            }
            if (!in_array($this->get('id'), $blocks_order)) {
                array_push($blocks_order, $this->get('id'));
                $column->blco_blocks_ordre = serialize($blocks_order);
                $column->save();
            }
        }

    }

}
