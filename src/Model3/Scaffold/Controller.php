<?php

namespace Model3\Scaffold;

use Model3\Controller\Controller as Model3Controller;

class Controller extends Model3Controller
{

    /**
     *
     * @var Model
     */
    protected $_sc;
    protected $_post = null;
    protected $_id = null;

    public function preDispatch()
    {
        $this->_sc->setView($this->view);
    }

    public function addAction()
    {
        if ($this->getRequest()->isPost())
        {
            if ($this->_post == null)
            {
                $this->_post = $this->getRequest()->getPost();
            }
            else
            {
                $this->_post = array_merge($this->getRequest()->getPost(), $this->_post);
            }
            $this->_sc->saveForm($this->_post);
        }
    }

    public function editAction()
    {
        if ($this->_id === null)
        {
            $params = $this->getRequest()->getParams();
            if (is_array($params) && count($params) > 0)
            {
                $keys = array_keys($params);
                $this->_id = $params[$keys[0]];
            }
        }
        if ($this->_id)
        {
            $this->_sc->setCurrentId($this->_id);
            if ($this->getRequest()->isPost())
            {
                if ($this->_post == null)
                    $this->_post = $this->getRequest()->getPost();
                else
                    $this->_post = array_merge($this->getRequest()->getPost(), $this->_post);
                $this->_sc->saveForm($this->_post);
            }
            return;
        }
    }

    public function deleteAction()
    {
        if ($this->_id === null)
        {
            $params = $this->getRequest()->getParams();
            if (is_array($params) && count($params) > 0)
            {
                $keys = array_keys($params);
                $this->_id = $params[$keys[0]];
            }
        }
        if ($this->_id)
        {
            $this->_sc->deleteRecord($this->_id);
            return;
        }
    }

    public function postDispatch()
    {
        $this->view->sc = $this->_sc;
    }

}