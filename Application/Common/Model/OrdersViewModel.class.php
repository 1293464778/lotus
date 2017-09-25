<?php
namespace Common\Model;
use Think\Model\ViewModel;


/**
 * 订单视图模型
 */
class OrdersViewModel extends ViewModel {
    public $viewFields = array(
        'Orders'=>array('*', '_type'=>'LEFT'),
        'Park'=>array('name'=>'parkName', 'provinceId', 'cityId', 'areaId', 'chargeId', '_on'=>'Orders.parkId=Park.id', '_type'=>'LEFT'),
        'Province'=>array('province', '_table'=>'__CHINA_PROVINCE__', '_on'=>'Province.provinceId=Park.provinceId', '_type'=>'LEFT'),
        'City'=>array('city', '_table'=>'__CHINA_CITY__', '_on'=>'City.cityId=Park.cityId', '_type'=>'LEFT'),
        'Area'=>array('area', '_table'=>'__CHINA_AREA__', '_on'=>'Area.areaId=Park.areaId', '_type'=>'LEFT'),
        'Type'=>array('name'=>'typeName', '_on'=>'Orders.type=Type.id', '_table'=>"__CAR_TYPE__"),
    );
}