<?php

namespace app\api\controller;

use app\api\repository\CateRepository;
use app\api\transform\CateTransform;
use app\ExController;
use think\App;
use think\db\exception\PDOException;
use \think\response\Json;


/**
 * Class RoleController
 * @package app\api\controller
 */
class CateController extends ExController
{
    /**
     * @var CateRepository
     */
    protected $repository;

    /**
     * @var CateTransform
     */
    protected $transform;

    /**
     * RoleController constructor.
     * @param CateRepository $repository
     * @param CateTransform $transform
     * @param $app
     */
    public function __construct(CateRepository $repository, CateTransform $transform, App $app)
    {
        parent::__construct($app);
        $this->repository = $repository;
        $this->transform = $transform;
    }

    /**
     * 获取全部数据
     *
     */
    public function getAllData(): Json
    {
        $allData = $this->repository->getFullData();
        return $this->retSucceed($allData);
    }

}
