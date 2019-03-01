<?php
/**
 * Form.php
 * BaiSam admin
 *
 * Created by realeff on 2018/10/04.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Support;


use BaiSam\UI\Form\Field;
use BaiSam\UI\Form\Builder;
use BaiSam\UI\Form\Group;
use BaiSam\UI\Form\Helper as FormHelper;
use BaiSam\UI\Form\Suite;
use BaiSam\UI\UIRepository;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Events\Dispatcher as EventDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

/**
 * Class Form
 *
 * @method Builder                              setId($id)
 * @method Builder                              setTitle($title)
 * @method Builder                              addClass($class)
 * @method Builder                              removeClass($class)
 * @method Builder                              attribute($name, $value = null)
 * @method Builder                              setView($view)
 * @method Builder                              forget($name)
 * @method \BaiSam\UI\Form\Field                field($name)
 * @method \BaiSam\UI\Form\Group                group($name, $callback = null)
 * @method \BaiSam\UI\Form\Field\Button         button($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Submit         submit($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Reset          reset($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Label          label($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Captcha        captcha($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Checkbox       checkbox($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Color          color($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Currency       currency($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Date           date($column, $label = '')
 * @method \BaiSam\UI\Form\Field\DateRange      daterange($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Decimal        decimal($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Editor         editor($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Email          email($column, $label = '')
 * @method \BaiSam\UI\Form\Field\File           file($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Html           html($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Image          image($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Ip             ip($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Number         number($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Password       password($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Phone          phone($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Radio          radio($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Select         select($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Slider         slider($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Switcher       switcher($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Tags           tags($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Text           text($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Textarea       textarea($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Url            url($column, $label = '')
 * @method \BaiSam\UI\Form\Field\Hidden         hidden($column)
 * @method \BaiSam\UI\Form\Field\DataSheet      datasheet($column, $label = '')
 *
 * @package BaiSam\Foundation
 */
class Form implements Renderable
{
    /**
     * 表单数据模型
     * @var Eloquent
     */
    protected $model;

    /**
     * 表单编译器
     * @var Builder
     */
    protected $builder;

    /**
     * @var FormHelper
     */
    protected $helper;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var Request
     */
    protected $request;

    /**
     * 映射字段
     * @var array
     */
    protected $maps = [];

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var Field\Submit
     */
    protected $submitButton;

    /**
     * @var Field\Button
     */
    protected $cancelButton;

    /**
     * Form constructor.
     *
     * @param array|string|null $action
     * @param string $method
     */
    public function __construct($action, $method)
    {
        if ( is_array($action) ) {
            list($action, $params) = $action;
            $action = $this->presentAction($action, $params);
        }
        else if ( ! empty($action) ) {
            $action = $this->presentAction($action);
        }
        else {
            $action = URL::current();
        }

        // 默认使用当前路径
        $this->builder = new Builder($action, $method);

        $this->submitButton = $this->builder->submit('submit', '提 交');
        $this->submitButton->ignoreRender()->color(UIRepository::STYLE_COLOR_DANGER);
        $this->cancelButton = $this->builder->button('cancel', '取 消');
        $this->cancelButton->ignoreRender()->color(UIRepository::STYLE_COLOR_WHITE);
    }

    /**
     * @return Eloquent
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * 设置表单的action,支持Controller@method和Route,
     * 如果是Route,则必需增加前缀route:,可以使用方法str_start($action, 'route:')
     *
     * @param string $action
     * @param array $parameters
     * @return $this
     */
    public function action($action, $parameters = [])
    {
        $action = $this->presentAction($action, $parameters);

        $this->builder->action($action);

        return $this;
    }

    /**
     * @param string $method
     * @return $this
     */
    public function method($method)
    {
        $this->builder->method($method);

        return $this;
    }

    /**
     * @param string $action
     * @param array $parameters
     * @return string
     */
    protected function presentAction($action, $parameters = []) {
        // 支持Controller@method and pathinfo
        if (strpos($action, '@') > 0) {
            return action($action, $parameters);
        }
        else if (strpos($action, '@') === 0) {
            return route(substr($action, 1), $parameters);
        }

        return url($action, $parameters);
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions($options)
    {
        foreach ($options as $name => $value) {
            $this->builder->{$name} = $value;
        }

        return $this;
    }

    /**
     * 仅在实例化时设置Form Helper
     *
     * @param FormHelper $helper
     * @return $this
     */
    public function setHelper(FormHelper $helper)
    {
        $this->helper = $helper;

        return $this;
    }

    /**
     * 仅在实例化时设置Event Dispatcher
     *
     * @param EventDispatcher $eventDispatcher
     * @return $this
     */
    public function setEventDispatcher(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    /**
     * 仅在实例化时设置Form Request
     *
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * 生成表单字段
     */
    public function buildForm()
    {
        //
    }

    /**
     * 映射数据到字段
     * @param string|array $field
     * @param string|array $key
     * @return $this
     */
    public function mapKeyToField($field, $key)
    {
        // 设置field column 与 model field name的映射关系
        if ( is_array($field) ) {
            $this->maps = array_merge( $this->maps, array_combine($field, $key) );
        }
        else if ( is_string($field) ) {
            $this->maps[$field] = $key;
        }

        return $this;
    }

    /**
     * 填充字段数据
     * @param Collection $fields 填充字段
     * @param array $data 填充数据
     * @param string $path 填充路径
     * @return $this
     */
    protected function fillData($fields, $data, $path = '')
    {
        $fields = $fields instanceof Collection ? $fields : collect($fields);

        $fields->map(function ($field) use ($data, $path) {
            if ($field instanceof Field && $field->ignored()) {
                return;
            }

            $value = null;
            if ( isset($this->maps[$path.$field->column()]) ) {
                $data_key = $this->maps[$path.$field->column()];

                $value = data_get($this->data, $data_key);
                if (is_null($value)) {
                    $value = data_get($data, $data_key);
                }
            }
            else if (isset($data)) {
                $value = data_get($data, $field->column());
            }

            if ($field instanceof Group) {
                $this->fillData( $field->fields(), $value, $path.$field->column().'.');
            }
            else if ($field instanceof Suite) {
                // 设置原始值
                $field->setOriginal($value);

                $this->fillData( $field->getAccessories(), $value, $path.$field->column().'.');
            }
            else {
                $field->setOriginal($value);
            }
        });

        return $this;
    }

    public function setModel($model)
    {
        //TODO 这里是否考虑绑定表单数据处理程序
        if ($model instanceof Eloquent) {
            //TODO relations
            $this->model = $model;
            $this->data = $model;
        }
        else {
            $this->data = $model;
        }

        return $this;
    }


    //TODO 编辑
    //TODO 查看

    //TODO TABS

    //TODO 处理相关内容
    //TODO setView

    /**
     * @return $this
     */
    public function disableSubmit()
    {
        if ($this->submitButton) {
            $this->builder->forget($this->submitButton->column());
        }

        $this->submitButton = null;

        return $this;
    }

    /**
     * @return Field\Submit
     */
    public function submitButton()
    {
        return $this->submitButton;
    }

    /**
     * @return $this
     */
    public function disableCancel()
    {
        if ($this->cancelButton) {
            $this->builder->forget($this->cancelButton->column());
        }

        $this->cancelButton = null;

        return $this;
    }

    /**
     * @return Field\Button
     */
    public function cancelButton()
    {
        return $this->cancelButton;
    }

    //TODO 添加\修改\查看\删除

    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render()
    {
        // 操作按钮
        $this->builder->submitButton = $this->submitButton;
        $this->builder->cancelButton = $this->cancelButton;

        // 装载数据
        if (isset($this->data)) {
            $this->fillData($this->builder->fields(), $this->data);
        }

        return $this->builder->render();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->builder, $method), $arguments);
    }
}