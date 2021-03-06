<?php
/**
 * 文件说明
 *
 * @filename    PrizeService.class.php
 * @author      zouzehua<zzh787272581@163.com>
 * @version     0.1.0
 * @since       0.1.0 11/22/15 oomusou: 新增getLatest3Posts()
 * @time        2016/6/27 22:54
 */

namespace Yboard;


class PrizeService extends CommonService {

    public function count($params = null) {
        $params      = $this->parseParams($params);
        $prize_model = $this->loadModel('Prize');

        return $prize_model->countByParams($params);
    }

    public function getList($params, $limit = 0, $page = 20) {
        $params      = $this->parseParams($params);
        $prize_model = $this->loadModel('Prize');
        $data        = $prize_model->getList($params, $limit, $page);
        foreach ($data as $k => $v) {
            $data[$k]['create_time'] = date('Y-m-d H:i', $v['create_time']);
        }

        return $data;
    }

    public function add($uid, $p_name, $p_inventory, $p_probability) {
        if (!$uid) {
            return $this->returnInfo(0, '用户id为空');
        }
        if (!$p_name) {
            return $this->returnInfo(0, '奖品名称为空');
        }
        if ((int)$p_inventory < 0) {
            return $this->returnInfo(0, '开始库存必须为正数');
        }


        $prizer_model = $this->loadModel('Prize');

        $prize_info = $prizer_model->getInfoByPrizeName($p_name);

        if ($prize_info) {
            return $this->returnInfo(0, '奖品已经存在');
        }
        $data        = array(
            'p_name'        => $p_name,
            'p_inventory'   => $p_inventory,
            'create_uid'    => $uid,
            'create_time'   => NOW_TIME,
            'p_probability' => $p_probability
        );
        $upload_data = $this->upload();
        if ($upload_data) {
            $data['p_image_uri'] = getFileUrl($upload_data['p_image_uri']['savepath'] . $upload_data['p_image_uri']['savename']);
        }
        if ($item_id = $prizer_model->save($data)) {

            return $this->returnInfo(1, '奖品添加成功');
        }

        return $this->returnInfo();
    }

    public function edit($p_id, $uid, $p_name, $p_inventory, $p_probability) {
        if (!$p_id) {
            return $this->returnInfo(0, '奖品id为空');
        }
        if (!$uid) {
            return $this->returnInfo(0, '用户id为空');
        }
        if (!$p_name) {
            return $this->returnInfo(0, '奖品名称为空');
        }

        $prizer_model = $this->loadModel('Prize');

        $prize_info = $prizer_model->getInfoByPrizeName($p_name);
        if ($prize_info && $prize_info['p_id'] != $p_id) {
            return $this->returnInfo(0, '奖品已经存在');
        }

        $data        = array(
            'p_name'      => $p_name,
            'p_inventory' => (int)$p_probability,
            'create_uid'  => $uid,
            'create_time' => NOW_TIME
        );
        $upload_data = $this->upload();
        if ($upload_data) {
            $data['p_image_uri'] = getFileUrl($upload_data['p_image_uri']['savepath'] . $upload_data['p_image_uri']['savename']);
        }
        if ($prizer_model->updateById($p_id, $data)) {
            $prizer_model->increaseInventory($p_id, $p_inventory);

            return $this->returnInfo(1, '奖品编辑成功');
        }

        return $this->returnInfo();
    }


    public function del($p_ids) {
        if (!$p_ids) {
            return $this->returnInfo(0, '请选择奖品id');
        }
        $prize_model = $this->loadModel('Prize');

        if ($prize_model->deleteByIds($p_ids) !== false) {
            return $this->returnInfo(1, '奖品删除成功');
        }

        return $this->returnInfo();
    }

    public function getPrizeById($p_id) {
        $prize_model = $this->loadModel('Prize');

        return $prize_model->getById($p_id);
    }
}