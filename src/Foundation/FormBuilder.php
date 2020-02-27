<?php
/**
 * FormBuilder.php
 * User: realeff
 * Date: 17-11-12
 */

namespace BaiSam\Foundation;


use BaiSam\UI\Form\Helper as FormHelper;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Events\Dispatcher as EventDispatcher;

class FormBuilder
{

    /**
     * @var Container
     */
    protected $app;

    /**
     * @var FormHelper
     */
    protected $helper;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * FormBuilder constructor.
     *
     * @param Container $container
     * @param FormHelper $helper
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(Container $container, FormHelper $helper, EventDispatcher $eventDispatcher)
    {
        $this->app = $container;
        $this->helper = $helper;
        $this->eventDispatcher = $eventDispatcher;

        //TODO 解决问题,组织form表单,初始化form设置及url,引用model数据,并将它绑定到form表单,触发事件,
    }

    /**
     * Create form
     *
     * @param string $formClass
     * @param array|string $options
     * @param Eloquent|array $model
     *
     * @return \BaiSam\Support\Form
     */
    public function create($formClass = null, $options = [], $model = null)
    {
        if (is_string($options)) {
            $options = ['action' => $options];
        }

        $action = Arr::get($options, 'action', null);
        $method = Arr::get($options, 'method', 'POST');

        if (is_null($formClass)) {
            $formClass = '\BaiSam\Support\Form';
        }

        //TODO 增加缓存，提供Build性能

        $form = $this->app->make($formClass, compact('action', 'method'));
        $form->setId(class_basename($formClass));

        $form->setHelper($this->helper);
        $form->setEventDispatcher($this->eventDispatcher);

        $form->setRequest($this->app->make('request'));

        if (isset($model)) {
            $form->setModel($model);
        }

        Arr::forget($options, ['action', 'method']);
        $form->setOptions($options);

        $form->buildForm();

        //TODO 支持表单客户端验证
        if ( Arr::get($options, 'client_validation', false) ) {
            $this->helper->getResource()->requireResource('form.validate');
        }

        //TODO 添加表单事件

        return $form;
    }

    /**
     * @return \BaiSam\UI\Form\Helper
     */
    public function getFormHelper()
    {
        return $this->helper;
    }

    /**
     * @return \Illuminate\Events\Dispatcher
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }


    public function view($formClass = null, Eloquent $model = null)
    {
        //TODO
    }
}