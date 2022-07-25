<?php
namespace app\index\Controller;
use think\Controller;

use Elasticsearch\ClientBuilder;
class Esc extends Controller
{
    public function _initialize()
    {
        $params['hosts'] = array(
            '1http://192.168.4.196:9200'
        );
        $this->client = new ClientBuilder();
    }
    public function index()
    {
        var_dump($this->client);
        echo 1;exit;
        return $this->fetch();
    }
}
