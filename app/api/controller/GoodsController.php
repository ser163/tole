<?php

namespace app\api\controller;

use app\api\repository\GoodsRepository;
use app\api\transform\GoodsTransform;
use app\ExController;
use think\App;
use app\model\Notes;
use think\db\exception\PDOException;
use think\facade\Db;
use \think\response\Json;


/**
 * Class RoleController
 * @package app\api\controller
 */
class GoodsController extends ExController
{
    /**
     * @var ItemsRepository
     */
    protected $repository;

    /**
     * @var ItemsTransform
     */
    protected $transform;

    /**
     * RoleController constructor.
     * @param GoodsRepository $repository
     * @param GoodsTransform $transform
     * @param App $app
     */
    public function __construct(GoodsRepository $repository, GoodsTransform $transform, App $app)
    {
        parent::__construct($app);
        $this->repository = $repository;
        $this->transform = $transform;
    }


}
