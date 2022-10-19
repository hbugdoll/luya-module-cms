<?php

namespace luya\cms\models;

use luya\admin\ngrest\base\NgRestModel;
use luya\admin\traits\SoftDeleteTrait;
use luya\cms\admin\Module;
use luya\cms\behaviours\WebsiteScopeBehavior;

/**
 * Represents the Navigation-Containers.
 *
 * @property string $name
 * @property string $alias
 * @property integer $website_id
 * @property bool $is_deleted
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class NavContainer extends NgRestModel
{
    use SoftDeleteTrait;

    public static function tableName()
    {
        return 'cms_nav_container';
    }

    public static function ngRestApiEndpoint()
    {
        return 'api-cms-navcontainer';
    }

    public static function findActiveQueryBehaviors()
    {
        return [
            'websiteScope' => WebsiteScopeBehavior::class
        ];
    }

    public function rules()
    {
        return [
            [['name', 'alias', 'website_id'], 'required'],
            [['website_id', 'is_deleted'], 'integer'],
        ];
    }

    public function scenarios()
    {
        return [
            'restcreate' => ['name', 'alias'],
            'restupdate' => ['name', 'alias'],
        ];
    }

    public function ngRestConfig($config)
    {
        $config->delete = true;

        $config->list->field('website_id', Module::t('model_navcontainer_website_label'))->selectModel(['modelClass' => Website::class, 'valueField' => 'id', 'labelField' => 'name']);
        $config->list->field('name', Module::t('model_navcontainer_name_label'))->text();
        $config->list->field('alias', Module::t('model_navcontainer_alias_label'))->text();

        $config->create->copyFrom('list');
        $config->update->copyFrom('list');

        $config->options = [
            'saveCallback' => "['ServiceMenuData', function(ServiceMenuData) { ServiceMenuData.load(true); }]",
        ];

        return $config;
    }

    /**
     * Relation returns all `cms_nav` rows belongs to this container sort by index without deleted or draf items.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNavs()
    {
        return $this->hasMany(Nav::class, ['nav_container_id' => 'id'])->where(['is_deleted' => false, 'is_draft' => false])->orderBy(['sort_index' => SORT_ASC]);
    }

    public function getWebsite()
    {
        return $this->hasOne(Website::class, ['website_id' => 'id']);
    }
}
