<?php

namespace App\Admin\Extensions\Grid\Displayers;

use Dcat\Admin\Grid\Displayers\Actions as BaseActions;

class Actions extends BaseActions
{
    public function display($callback = null)
    {
        $this->addViewAction();
        $this->addEditAction();
        $this->addDeleteAction();

        return parent::display($callback);
    }

    protected function addViewAction()
    {
        $key = $this->getKey();
        $resource = url()->current();
        $url = "{$resource}/{$key}";

        $this->prepend(
            "<a href='{$url}' style='color:white;margin:4px' class='btn btn-info'><i class='feather icon-eye'></i> {$this->trans('view')}</a>"
        );
    }

    protected function addEditAction()
    {
        $key = $this->getKey();
        $resource = url()->current();
        $url = "{$resource}/{$key}/edit";

        $this->append(
            "<a href='{$url}' style='color:white;margin:4px' class='btn btn-primary'><i class='feather icon-edit'></i> {$this->trans('edit')}</a>"
        );
    }

    protected function addDeleteAction()
    {
        $key = $this->getKey();
        $resource = url()->current();
        $url = "{$resource}/{$key}";

        $this->append(
            "<a data-url='{$url}' data-message='ID - {$key}' data-action='delete' data-redirect='{$resource}?_pjax=%23pjax-container' style='color:white;margin:4px' href='javascript:void(0)' class='btn btn-danger'><i class='feather icon-trash grid-action-icon' title='{$this->trans('delete')}'></i> {$this->trans('delete')}</a>"
        );
    }
}