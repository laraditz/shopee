<?php

namespace Laraditz\Shopee\Services;

use Laraditz\Shopee\Models\ShopeeReturn;
use Laraditz\Shopee\Models\ShopeeRequest;

class ReturnsService extends BaseService
{
    public function afterGetReturnListResponse(ShopeeRequest $request, ?array $result = [])
    {
        $return_list = data_get($result, 'response.return');

        if (is_array($return_list) && count($return_list) > 0) {
            foreach ($return_list as $return) {
                if (is_array($return)) {
                    ShopeeReturn::syncFromPayload($return, $request->shop_id);
                }
            }
        }
    }

    public function afterGetReturnDetailResponse(ShopeeRequest $request, ?array $result = [])
    {
        $return = data_get($result, 'response');

        if (is_array($return)) {
            ShopeeReturn::syncFromPayload($return, $request->shop_id);
        }
    }
}
