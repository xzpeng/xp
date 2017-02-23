<?php

namespace App\Admin\Controllers;

use App\Models\Strategy;
use App\Models\Host;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class StrategyController extends Controller
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

            $content->header('header');
            $content->description('description');

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

            $content->header('header');
            $content->description('description');

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

            $content->header('header');
            $content->description('description');

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
        return Admin::grid(Strategy::class, function (Grid $grid) {

            $grid->id('ID')->sortable();

            $grid->created_at();
            $grid->updated_at();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Strategy::class, function (Form $form) {

            $form->display('id', 'ID');

            $form->select('host_id')->options(function($id) {
                return Host::options($id);
            });

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }


    protected function formAddUser()
    {
        return Admin::form(Strategy::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->hidden('host_id', $id);

            $form->select('host_id')->options(function($id) {
                return Host::options($id);
            });
            $form->ignore();

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }


    public function addUser($id)
    {
        return Admin::content(function (Content $content) use($id) {

            $content->header('header');
            $content->description('description');

            $content->body($this->form());
        });

        // echo 'addUser',$id;
    }

    public function delUser($id)
    {
        echo 'delUser',$id;
    }

    public function addProcess($id)
    {
        echo 'addProcess',$id;
    }

    public function delProcess($id)
    {
        echo 'delProcess',$id;
    }

    public function addFile($id)
    {
        echo 'addFile',$id;
    }

    public function delFile($id)
    {
        echo 'delFile',$id;
    }
}
