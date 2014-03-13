<?php


namespace Ip\Internal\Content;


class Filter
{
    public static function ipWidgets($widgets)
    {

        $widgets['Title'] = new \Ip\Internal\Content\Widget\Title\Controller('Title', 'Content', 1);
        $widgets['Text'] = new \Ip\Internal\Content\Widget\Text\Controller('Text', 'Content', 1);
        $widgets['Divider'] = new \Ip\Internal\Content\Widget\Divider\Controller('Divider', 'Content', 1);
        $widgets['Image'] = new \Ip\Internal\Content\Widget\Image\Controller('Image', 'Content', 1);
        $widgets['Gallery'] = new \Ip\Internal\Content\Widget\Gallery\Controller('Gallery', 'Content', 1);
        $widgets['File'] = new \Ip\Internal\Content\Widget\File\Controller('File', 'Content', 1);
        $widgets['Html'] = new \Ip\Internal\Content\Widget\Html\Controller('Html', 'Content', 1);
        $widgets['Columns'] = new \Ip\Internal\Content\Widget\Columns\Controller('Columns', 'Content', 1);
        $widgets['Form'] = new \Ip\Internal\Content\Widget\Form\Controller('Form', 'Content', 1);
        $widgets['Video'] = new \Ip\Internal\Content\Widget\Video\Controller('Video', 'Content', 1);
        $widgets['Map'] = new \Ip\Internal\Content\Widget\Map\Controller('Map', 'Content', 1);


        $widgetDirs = static::getPluginWidgetDirs();
        foreach ($widgetDirs as $widgetDirRecord) {
            $widgetKey = $widgetDirRecord['widgetKey'];
            $widgetClass = '\\Plugin\\' . $widgetDirRecord['module'] . '\\' . Model::WIDGET_DIR . '\\' . $widgetKey . '\\Controller';
            if (class_exists($widgetClass)) {
                $widget = new $widgetClass($widgetKey, $widgetDirRecord['module'], 0);
            } else {
                $widget = new \Ip\WidgetController($widgetKey, $widgetDirRecord['module'], 0);
            }
            $widgets[$widgetDirRecord['widgetKey']] = $widget;
        }
        return $widgets;
    }

    /**
     * Form widget
     * @param array $value
     */
    public static function ipWidgetFormFieldTypes($fieldTypes, $info = null)
    {

        $typeText = __('Text', 'ipAdmin', false);
        $typeEmail = __('Email', 'ipAdmin', false);
        $typeTextarea = __('Textarea', 'ipAdmin', false);
        $typeSelect = __('Select', 'ipAdmin', false);
        $typeCheckbox = __('Checkbox', 'ipAdmin', false);
        $typeRadio = __('Radio', 'ipAdmin', false);
        $typeCaptcha = __('Captcha', 'ipAdmin', false);
        $typeFile = __('File', 'ipAdmin', false);

        $fieldTypes['Text'] = new FieldType('Text', '\Ip\Form\Field\Text', $typeText);
        $fieldTypes['Email'] = new FieldType('Email', '\Ip\Form\Field\Email', $typeEmail);
        $fieldTypes['Textarea'] = new FieldType('Textarea', '\Ip\Form\Field\Textarea', $typeTextarea);
        $fieldTypes['Select'] = new FieldType('Select', '\Ip\Form\Field\Select', $typeSelect, 'ipWidgetForm_InitListOptions', 'ipWidgetForm_SaveListOptions', ipView(
            'view/formFieldOptions/list.php'
        )->render());
        $fieldTypes['Checkbox'] = new FieldType('Checkbox', '\Ip\Form\Field\Checkbox', $typeCheckbox, 'ipWidgetForm_InitWysiwygOptions', 'ipWidgetForm_SaveWysiwygOptions', ipView(
            'view/formFieldOptions/wysiwyg.php', array('form' => self::wysiwygForm())
        )->render());
        $fieldTypes['Radio'] = new FieldType('Radio', '\Ip\Form\Field\Radio', $typeRadio, 'ipWidgetForm_InitListOptions', 'ipWidgetForm_SaveListOptions', ipView(
            'view/formFieldOptions/list.php'
        )->render());
        $fieldTypes['Captcha'] = new FieldType('Captcha', '\Ip\Form\Field\Captcha', $typeCaptcha);
        $fieldTypes['File'] = new FieldType('File', '\Ip\Form\Field\File', $typeFile);

        return $fieldTypes;
    }

    private static function wysiwygForm()
    {
        $form = new \Ip\Form();
        $form->setEnvironment(\Ip\Form::ENVIRONMENT_ADMIN);
        $field = new \Ip\Form\Field\RichText(array (
            'name' => 'text'
        ));
        $form->addField($field);
        return $form;
    }

    private static function getPluginWidgetDirs()
    {
        $answer = array();
        $plugins = \Ip\Internal\Plugins\Service::getActivePluginNames();
        foreach ($plugins as $plugin) {
            $answer = array_merge($answer, static::findPluginWidgets($plugin));
        }
        return $answer;
    }

    private static function findPluginWidgets($moduleName)
    {
        $widgetDir = ipFile('Plugin/' . $moduleName . '/' . Model::WIDGET_DIR . '/');

        if (!is_dir($widgetDir)) {
            return array();
        }
        $widgetFolders = scandir($widgetDir);
        if ($widgetFolders === false) {
            return array();
        }

        $answer = array();
        //foreach all widget folders
        foreach ($widgetFolders as $widgetFolder) {
            //each directory is a widget
            if (!is_dir($widgetDir . $widgetFolder) || $widgetFolder == '.' || $widgetFolder == '..') {
                continue;
            }
            if (isset ($answer[(string)$widgetFolder])) {
                ipLog()->warning(
                    'Content.duplicateWidget: {widget}',
                    array('plugin' => 'Content', 'widget' => $widgetFolder)
                );
            }
            $answer[] = array(
                'module' => $moduleName,
                'dir' => $widgetDir . $widgetFolder . '/',
                'widgetKey' => $widgetFolder
            );
        }
        return $answer;
    }


    public static function ipAdminNavbarButtons($buttons, $info)
    {
        $breadcrumb = ipContent()->getBreadcrumb();
        if (!empty($breadcrumb[0])) {
            $rootPage = $breadcrumb[0];
            $menu = ipContent()->getPage($rootPage->getParentId());
            $alias = $menu->getAlias();
        } else {
            $alias = '';
        }


        if (ipContent()->getCurrentPage()) {
            if (ipIsManagementState()) {
                $buttons[] = array(
                    'text' => __('Preview', 'ipAdmin', false),
                    'hint' => __('Hides admin tools', 'ipAdmin', false),
                    'class' => 'ipsContentPreview',
                    'faIcon' => 'fa-eye',
                    'url' => '#'
                );
            } else {
                $buttons[] = array(
                    'text' => __('Edit', 'ipAdmin', false),
                    'hint' => __('Show widgets', 'ipAdmin', false),
                    'class' => 'ipsContentEdit',
                    'faIcon' => 'fa-edit',
                    'url' => '#'
                );
            }
            $buttons[] = array(
                'text' => __('Settings', 'ipAdmin', false),
                'hint' => __('Page settings', 'ipAdmin', false),
                'class' => 'ipsAdminPageSettings',
                'faIcon' => 'fa-gear',
                'url' => ipActionUrl(array('aa' => 'Pages.index')) . '#hash&language=' . ipContent()->getCurrentLanguage()->getCode() . '&menu=' . $alias . '&page=' . ipContent()->getCurrentPage()->getId()
            );
        }

        return $buttons;
    }


    public static function ipAdminNavbarCenterElements($elements, $info)
    {
        if (ipContent()->getCurrentPage()) {
            $revision = \Ip\ServiceLocator::content()->getCurrentRevision();
            $revisions = \Ip\Internal\Revision::getPageRevisions(ipContent()->getCurrentPage()->getId());

            $managementUrls = array();
            $currentPageLink = ipContent()->getCurrentPage()->getLink();
            foreach ($revisions as $value) {
                $managementUrls[] = $currentPageLink . '?_revision=' . $value['revisionId'];
            }

            $data = array(
                'revisions' => $revisions,
                'currentRevision' => $revision,
                'managementUrls' => $managementUrls,
                'isPublished' => !\Ip\Internal\Content\Model::isRevisionModified($revision['revisionId']) && ipContent()->getCurrentPage()->isVisible()
            );

            $elements[] = ipView('view/publishButton.php', $data);
        }
        return $elements;
    }
}
