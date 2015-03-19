<?php

use \Behat\MinkExtension\Context\MinkContext;


/** Class FeatureContext */
class FeatureContext extends MinkContext
{
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }


    /**
     * @param $page
     */
    public function visit($page)
    {
        parent::visit($page);
        //Костыль, чтобы доджаться первой загрузки системы, это надо бы номрально исправить.
        $this->waitForExtReady();
    }


    /**
     * @param $gridHeaderLabel
     * @param $gridHeaderValue
     * @param $gridOperation
     * @When /^в гриде нахожу строку со значением "(?P<gridHeaderValue>(?:[^"]|\\")*)" в столбце "(?P<gridHeaderLabel>(?:[^"]|\\")*)" и нажимаю операцию "(?P<gridOperation>(?:[^"]|\\")*)"$/
     */
    public function gridOperation($gridHeaderLabel, $gridHeaderValue, $gridOperation)
    {
        $gridHeaderLabel = $this->handleVariables($gridHeaderLabel);
        $gridHeaderValue = $this->handleVariables($gridHeaderValue);
        $gridOperation = $this->handleVariables($gridOperation);

        //.x-grid3-hd.x-grid3-cell - кнопки заголовка грида
        $script = '
            var __colNumber = 0;
            $(\'.x-grid3-hd.x-grid3-cell\').each(function(i,el){
                if($(this).text()=== \'' . $gridHeaderLabel . '\'){
                       __colNumber = i;
                };
            })


            $(\'.x-grid3-col.x-grid3-cell.x-grid3-td-\'+__colNumber+\'>div\').filter(function(){
                return $(this).text() === \'' . $gridHeaderValue . '\';
            }).parents(\'table\').find(\'td.x-action-col-cell img\').filter(function(){
                return $(this).attr(\'alt\') === \'' . $gridOperation . '\';
            }).click()
        ';

        $this->getSession()->executeScript($script);
    }


    /**
     * @param bool $fieldset
     * @param $field
     * @param $value
     * @When /^в филдсете "(?P<fieldset>(?:[^"]|\\")*)" выбираю в поле "(?P<field>(?:[^"]|\\")*)" значение "(?P<value>(?:[^"]|\\")*)"$/
     */
    public function ipFillFieldOption3($fieldset = false, $field, $value)
    {
        // TODO Вероятно Ext.getCmp(__id).onTriggerClick(); поможет во многих остальных случаях или позволит сделать универсальный селект

        $script = '
            var fieldset = $(\'fieldset\').filter(function(){
                return $(this).children(\'legend\').text()=== \'' . $fieldset . '\';
            })

            var __id = fieldset.find(\'label\').filter(function(){
                return $(this).text() === \'' . $field . ':\';
            }).attr(\'for\')

            Ext.getCmp(__id).onTriggerClick();
        ';


        $this->getSession()->executeScript($script);

        sleep(2);

        $script = '
                   $(\'div.x-combo-list[style*="visibility: visible"]  div.x-combo-list-inner>div\').filter(function() {
                        return $(this).text().trim() === \'' . $value . '\';
                    }).click().select();

                   var __id = $(\'label\').filter(function(){
                       return $(this).text() === \'' . $field . ':\';
                   }).attr(\'for\')

                    Ext.getCmp(__id).fireEvent(\'change\',Ext.getCmp(__id));
                    $("#"+__id).change().blur();

        ';


        $this->getSession()->executeScript($script);

    }


    /**
     * @param $field
     * @param $value
     * @When /^выбираю "(?P<value>(?:[^"]|\\")*)" в поле "(?P<field>(?:[^"]|\\")*)" с автодополнением$/
     */
    public function selectOption2($field, $value)
    {
        $strippedVal = mb_substr($value, 0, -1);

        // TODO Вероятно Ext.getCmp(__id).onTriggerClick(); поможет во многих остальных случаях или позволит сделать универсальный селект
        $script = '
                    var __id = $(\'label\').filter(function(){
                       return $(this).text() === \'' . $field . ':\';
                   }).attr(\'for\')

                    Ext.getCmp(__id).setValue(\'' . $strippedVal . '\')
                    Ext.getCmp(__id).onTriggerClick();

        ';

        $this->getSession()->executeScript($script);

        sleep(2);

        $script = '
                   $(\'div.x-combo-list[style*="visibility: visible"]  div.x-combo-list-inner>div\').filter(function() {
                        return $(this).text().trim() === \'' . $value . '\';
                    }).click().select();

                   var __id = $(\'label\').filter(function(){
                       return $(this).text() === \'' . $field . ':\';
                   }).attr(\'for\')

                    Ext.getCmp(__id).fireEvent(\'change\',Ext.getCmp(__id));
                    $("#"+__id).change().blur();

        ';


        $this->getSession()->executeScript($script);
    }


    /**
     * @param $select
     * @param $option
     */
    public function selectOption($select, $option)
    {
        $script = '
            var __id = $(\'label\').filter(function(){
                return $(this).text() === \'' . $select . ':\';
            }).attr(\'for\')

            $(\'#\'+__id).click();

            $(\'div.x-combo-list[style*="visibility: visible"]  div.x-combo-list-item\').filter(function() {
                return $(this).text() === \'' . $option . '\';
            }).click();
            $(\'#\'+__id).blur();
        ';

        $this->getSession()->executeScript($script);
    }


    /**
     * @param $tab
     * @When /^перехожу во вкладку "(?P<tab>(?:[^"]|\\")*)"$/
     */
    public function iGoToTab($tab)
    {
        $script = '
            var __id = $(\'span.x-tab-strip-text:contains("' . $tab . '")\').parents(\'li\').attr(\'id\');
            var ids = __id.split(\'__\');
            Ext.getCmp(ids[0]).setActiveTab(ids[1]);

        ';

        $this->getSession()->executeScript($script);


//Если надо будет проверять урл - активируем этот код
//        //region Селениум чет не дружит с хешами, поэтому приходится клик оборачивать вот в такой костыль
//        $before = $session->getCurrentUrl();
//        $element->click();
//        sleep(2);//Меньше двух - у меня не успевает обновиться урл
//        $now = $session->getCurrentUrl();
//
//        if ($before != $now) {
//            $this->getSession()->executeScript('window.location = "' . $now . '";');
//        }
//        //endregion
    }


    /**
     * @param $fieldset
     * @param $field
     * @param $value
     * @When /^в филдсете "(?P<fieldset>(?:[^"]|\\")*)" заполняю поле "(?P<field>(?:[^"]|\\")*)" значением "(?P<value>(?:[^"]|\\")*)"$/
     */
    public function fillFieldInFieldset($fieldset, $field, $value)
    {
        $script = '
            var fieldset = $(\'fieldset\').filter(function(){
                return $(this).children(\'legend\').text()=== \'' . $fieldset . '\';
            })

            var __id = fieldset.find(\'label\').filter(function(){
                return $(this).text() === \'' . $field . ':\';
            }).attr(\'for\')

            Ext.getCmp(__id).setValue(\'' . $value . '\');
        ';

        $this->getSession()->executeScript($script);

        sleep(2);

        $script = '
            var fieldset = $(\'fieldset\').filter(function(){
                return $(this).children(\'legend\').text()=== \'' . $fieldset . '\';
            })

            var __id = fieldset.find(\'label\').filter(function(){
                return $(this).text() === \'' . $field . ':\';
            }).attr(\'for\')

            Ext.getCmp(__id).fireEvent(\'change\',Ext.getCmp(__id));
        ';

        $this->getSession()->executeScript($script);
    }


    /**
     * @param $field
     * @param $value
     * @When /^заполняю поле типа телефон "(?P<field>(?:[^"]|\\")*)" значением "(?P<value>(?:[^"]|\\")*)"$/
     */
    public function fillFieldPhone($field, $value)
    {
        list($countryCode, $cityCode, $phone) = explode('-', $value);
        $script = '
            var __id = $(\'label\').filter(function(){
                return $(this).text() === \'' . $field . ':\';
            }).attr(\'for\')

            var values = [' . $countryCode . ',' . $cityCode . ',' . $phone . '];
            $(\'#\'+__id).siblings(\'input\').each(function(i,el){
                $(el).val(values[i])
            });
        ';

        $this->getSession()->executeScript($script);
    }

    /**
     * Просто добавил blur
     *
     * @param $fieldLocator
     * @param $value
     * @throws
     */
    public function fillField($fieldLocator, $value)
    {
        $fieldLocator = $this->handleVariables($fieldLocator);
        $value = $this->handleVariables($value);

        $fieldLocator = $this->fixStepArgument($fieldLocator);
        $value = $this->fixStepArgument($value);

        $field = $this->getSession()->getPage()->findField($fieldLocator);

        if (null === $field) {
            throw new \Exception('form field id|name|label|value ' . $fieldLocator);
        }


        $field->focus();
        $field->setValue($value);
        $field->blur();


    }


    /**
     *
     * Это и кнопка и меню
     *
     * @When /^(?:|I )Menu "(?P<locator>(?:[^"]|\\")*)"$/
     * @When /^выбираю меню "(?P<locator>(?:[^"]|\\")*)"$/
     * @param $locator
     */
    public function pressButton($locator)
    {
        sleep(1);
        //TODO Прикрутить проверку, если OK набрано не на том языке

        $session = $this->getSession();

        //Пытаемся найти кнопку с текстом locator внутри
        $xpath = '//button[text() = \'' . $locator . '\']';
        $element = $session->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', $xpath)
        );

        //Если кнопку не нашли - ищем элемент меню (они в дропдауне имеют класс x-menu-item) с текстом locator внутри
        if (null === $element) {
            $xpath = '//a[contains(@class, \'x-menu-item\')]/span[text() = \'' . $locator . '\']';
            $element = $session->getPage()->find(
                'xpath',
                $session->getSelectorsHandler()->selectorToXpath('xpath', $xpath)
            );

        }

        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Could not evaluate XPath: "%s"', $xpath));
        }


        //region Селениум чет не дружит с хешами, поэтому приходится клик оборачивать вот в такой костыль
        $before = $session->getCurrentUrl();
        $element->click();
        sleep(2);//Меньше двух - у меня не успевает обновиться урл
        $now = $session->getCurrentUrl();

        if ($before != $now) {
            $this->getSession()->executeScript('window.location = "' . $now . '";');
        }
        //endregion

    }


    /**
     * @When /^(?:|I )wait "(?P<seconds>(?:[^"]|\\")*)" seconds$/
     * @When /^жду "(?P<menuName>(?:[^"]|\\")*)" секунд$/
     * @param $seconds
     */
    public function wait($seconds)
    {
        sleep($seconds);
    }


    /**
     * @param $name
     * @When /^(?:|I )make srceenshot "(?P<name>(?:[^"]|\\")*)"/
     * @When /^делаю скриншот "(?P<name>(?:[^"]|\\")*)"$/
     */
    public function makeScreenShot($name)
    {
        $this->saveScreenshot($name . '.png', 'features' . DIRECTORY_SEPARATOR . 'screenshots' . DIRECTORY_SEPARATOR);
    }

    /**
     * @When /^Wait for ext ready$/
     * @throws
     */
    public function waitForExtReady()
    {
        //TODO переделаете по нормальному
        sleep(5);
    }


    /**
     * @param $name
     * @param $value
     * @throws Exception
     * @When /^записываю "(?P<value>(?:[^"]|\\")*)" в "(?P<name>(?:[^"]|\\")*)"$/
     */
    public function setVariable($name, $value)
    {
        if (substr($name, 0, 1) != '?') {
            throw new \Exception('Context variable must start from "?" You wrote:' . $value);
        } elseif (FALSE !== strpos($name, ' ')) {
            throw new \Exception('Context variable must not contain spaces. You wrote:' . $value);
        } else {
            if (substr($value, 0, 3) == 'php') {
                $value = substr($value, 3);
                var_dump($value);
                eval($value);
            };
            $name = substr($name, 1);
            $this->variables[$name] = $value;
        }

    }


    /**
     * Заменяет названия на значения
     * @param $value
     * @return mixed
     * @throws Exception
     */
    protected function handleVariables($value)
    {
        if (substr($value, 0, 1) == '?') {
            $name = substr($value, 1);
            if (array_key_exists($name, $this->variables)) {
                return $this->variables[$name];
            } else {
                throw new \Exception('Undefined context variable ' . $value);
            }
        } else {
            return $value;
        }
    }

    /** @var array */
    public $variables = array();

}
