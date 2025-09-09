<div class="container">
    <div class="box">
        <h2>{{ __('Shopee Seller Authorized!') }}</h2>
        @if($shop->accessToken)
        <p>{{ __('Access token has been generated. You may now proceed to call any supported Shopee API using this SDK.') }}</p>
        @endif
        <ul>
            <li><strong>{{ __('Authorization code') }}</strong>: {{ $code }}</li>
            @if($shop)
                @if($shop->name)
                    <li><strong>{{ __('Shop name') }}</strong>: {{ $shop->name }}</li>
                @endif
                <li><strong>{{ __('Shop ID') }}</strong>: {{ $shop->id }}</li>
                <li><strong>{{ __('Shop Region') }}</strong>: {{ $shop->region }}</li>
                @if($shop->accessToken)
                    <li><strong>{{ __('Access token') }}</strong>: {{ $shop->accessToken->access_token }} </li>                   
                    <li><strong>{{ __('Refresh Token') }}</strong>: {{ $shop->accessToken->refresh_token }}</li>   
                    <li><strong>{{ __('Access token expires at') }}</strong>: {{ $shop->accessToken?->expires_at?->toDateTimeString() }} </li>                 
                @endif
            @endif
        </ul>
    
    </div>
    </div>
    
    <style>
    .container{
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 10px;
        padding-top: 20px;
        font-family: "Trebuchet MS", Helvetica, Verdana, sans-serif;
    }
    .box {
        border: #cccccc 1px solid;
        padding: 30px 20px;
        border-radius: 15px;
        width: 700px;
        max-width: 100%;
    }
    
    h2 {
        margin: 0;
        margin-bottom: 10px;
    }
    
    ul{
        margin: 0;
        padding: 0;
    }
    
    ul > li {
        list-style-type: none;
        line-height: 1.5;
        word-break: break-all;
    }
    </style>