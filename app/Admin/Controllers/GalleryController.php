<?php

namespace App\Admin\Controllers;

use App\Models\Gallery;
use App\Models\Category;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class GalleryController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('图片库');
            $content->description('列表展示');

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('图片库');
            $content->description('编辑');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('图片库');
            $content->description('添加');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Gallery::class, function (Grid $grid) {
            $grid->model()->orderby('id', 'desc');
            $grid->id('ID')->sortable();
            $grid->column('category.title', '分类名称')->badge('green');
            $grid->title('名称');
            // $grid->value('图片')->image();
            $grid->value('图片')->display(function ($img) {
              $img = preg_match('/http/', $img) ? $img : config('app.url'). '/uploads/' .$img;
              return '<img src="'. $img .'" style="width:180px;height:150px;">';
            });
            $grid->description('简述')->display(function ($str) {
              return '<div style="width:250px;height:120px;">'. $str . '</div>';
            });
            $grid->click_num('数据信息')->display(function($click_num) {
              return '点击量: '.$this->click_num.'<br/>'.'使用量: '.$this->use_num;
            });
            $states = [
              ['text' => '启用', 'value' => '1', 'color' => 'success'],
              ['text' => '禁用', 'value' => '０', 'color' => 'danger'],
            ];
            $grid->status('状态')->switch($states);
            $grid->created_at('创建时间');
            $grid->disableExport();
            $grid->filter(function ($filter) {
                // 去掉默认的id过滤器
                $filter->disableIdFilter();

                // 在这里添加字段过滤器
                $filter->where(function($query) {
                  $query->where('title', 'like', "%{$this->input}%")->orWhere('description', 'like', "%{$this->input}%");
                }, '名称或简述');
                $filter->where(function($query) {
                  $query->whereHas('category', function($query) {
                    $query->where('title', 'like', "%{$this->input}%");
                  });
                }, '分类名称')->select(Category::buildSelectOptions($nodes = [], $parentId = Category::TUPIAN_PID, $prefix = ''));;
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Gallery::class, function (Form $form) {

            $form->display('id', 'ID');
            // $form->select('category_id', '分类')->options(Category::where('parent_id', Category::TUPIAN_PID)->get()->pluck('title', 'id'));
            $form->select('category_id', '分类')->options(Category::buildSelectOptions($nodes = [], $parentId = Category::TUPIAN_PID, $prefix = ''));
            $form->text('title', '名称');
            $form->image('value', '图片');
            $form->textarea('description', '简述')->rules('nullable')->help('**可不填**');
            $form->number('click_num', '点击量')->default(0);
            $form->number('use_num', '使用量')->default(0);
            $states = [
              'on' => ['value' => '1', 'text' => '可用', 'color' => 'success'],
              'off' => ['value' => '0', 'text' => '禁用', 'color' => 'danger']
            ];
            $form->switch('status', '状态')->states($states)->default(1);
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '编辑时间');
        });
    }
}
