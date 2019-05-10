<?php
ini_set('date.timezone', 'Asia/Shanghai');
//error_reporting(E_ERROR);
require_once __DIR__ . "/../lib/WxPay.Api.php";
require_once __DIR__ . '/../lib/WxPay.Notify.php';
require_once __DIR__ . '/log.php';

//初始化日志
$logHandler = new CLogFileHandler("../logs/" . date('Y-m-d') . '.log');
$log = LogByWx::Init($logHandler, 15);

class PayNotifyCallBack extends WxPayNotify
{
    //查询订单
    public function Queryorder($out_trade_no)
    {
        $input = new WxPayOrderQuery();
        $input->SetOut_trade_no($out_trade_no);
        $result = WxPayApi::orderQuery($input);
        LogByWx::DEBUG("query:" . json_encode($result));
        if (array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS"
        ) {
            return $result;
        }
        return false;
    }

    //关闭订单
    public function Closeorder($out_trade_no)
    {
        $input = new WxPayOrderQuery();
        $input->SetOut_trade_no($out_trade_no);
        $result = WxPayApi::closeOrder($input);
        LogByWx::DEBUG("query:" . json_encode($result));
        if (array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == 'SUCCESS'
            && empty($result['err_code'])
        ) {
            return true;
        } else {
            return false;
        }
    }

    //重写回调处理函数
    public function NotifyProcess($data, &$msg)
    {
        LogByWx::DEBUG("call back:" . json_encode($data));
        $notfiyOutput = array();

        if (!array_key_exists("transaction_id", $data)) {
            $msg = "输入参数不正确";
            return false;
        }
        //查询订单，判断订单真实性
        if (!$this->Queryorder($data["transaction_id"])) {
            $msg = "订单查询失败";
            return false;
        }
        return array('status' => 1, 'msg' => '支付成功');
    }
}

LogByWx::DEBUG("begin notify");
$notify = new PayNotifyCallBack();
//$notify->Handle(false);