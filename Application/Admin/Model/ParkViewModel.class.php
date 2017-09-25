<?php
namespace Admin\Model;
use Think\Model\ViewModel;

/**
 * 停车场视图
 */
class ParkViewModel extends ViewModel {
        public $viewFields = array(
            'park'=>array('*', '_type'=>'LEFT'),
            'province'=>array('province','_table'=>'__CHINA_PROVINCE__', '_on'=>'park.provinceId=province.provinceId', '_type'=>'LEFT'),
            'city'=>array('city','_table'=>'__CHINA_CITY__', '_on'=>'park.cityId=city.cityId', '_type'=>'LEFT'),
            'area'=>array('area', '_table'=>'__CHINA_AREA__', '_on'=>'park.areaId=area.areaId', '_type'=>'LEFT'),
            'charge'=>array('name'=>'charge', '_table'=>'__CHARGE__', '_on'=>'park.chargeId=charge.id', '_type'=>'LEFT')
        );
}