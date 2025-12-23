<template>
  <view class="page flex-col">
    <view class="group_31 flex-row">
      <!-- 左侧功能区 -->
      <view class="block_29 flex-col">
        <!-- 顶部状态栏 -->
        <view class="box_38 flex-row">
          <text class="text_1">9:41</text>
          <image
            class="thumbnail_1"
            referrerpolicy="no-referrer"
            src="/static/img/status-signal.png"
          />
          <image
            class="thumbnail_2"
            referrerpolicy="no-referrer"
            src="/static/img/status-wifi.png"
          />
          <image
            class="image_1"
            referrerpolicy="no-referrer"
            src="/static/img/status-battery.png"
          />
        </view>

        <!-- 标题栏 -->
        <view class="box_39 flex-row justify-between">
          <view class="image-text_61 flex-row justify-between">
            <image
              class="label_1"
              referrerpolicy="no-referrer"
              src="/static/img/app-logo.png"
            />
            <text class="text_2">Second&nbsp;Futures</text>
          </view>
          <image
            class="label_2"
            referrerpolicy="no-referrer"
            src="/static/img/icon-notification.png"
          />
        </view>

        <!-- Banner区域 -->
        <view class="box_4 flex-col"></view>

        <!-- 功能区域 -->
        <view class="box_40 flex-row justify-between">
          <!-- 推广中心 -->
          <view class="section_14 flex-col">
            <image
              class="image_15"
              referrerpolicy="no-referrer"
              src="/static/img/banner-promotion.png"
            />
            <view class="box_34 flex-row">
              <view class="image-text_62 flex-row justify-between">
                <image
                  class="label_17"
                  referrerpolicy="no-referrer"
                  src="/static/img/icon-promotion.png"
                />
                <view class="text-group_77 flex-col">
                  <text class="text_43">推广中心</text>
                  <text class="text_44">Promotion</text>
                </view>
              </view>
            </view>
          </view>

          <!-- 下载中心和玩法介绍 -->
          <view class="section_15 flex-col">
            <!-- 下载中心 -->
            <view class="box_35 flex-row">
              <view class="image-text_63 flex-row justify-between">
                <image
                  class="label_18"
                  referrerpolicy="no-referrer"
                  src="/static/img/icon-download.png"
                />
                <view class="text-group_78 flex-col">
                  <text class="text_45">下载中心</text>
                  <text class="text_46">Download</text>
                </view>
              </view>
            </view>

            <!-- 玩法介绍 -->
            <view class="box_36 flex-row justify-between">
              <image
                class="image_16"
                referrerpolicy="no-referrer"
                src="/static/img/icon-intro.png"
              />
              <view class="text-group_79 flex-col">
                <text class="text_47">玩法介绍</text>
                <text class="text_48">Introduction</text>
              </view>
            </view>
          </view>
        </view>

        <!-- 活动福利 -->
        <view class="box_37 flex-row justify-between">
          <image
            class="image_17"
            referrerpolicy="no-referrer"
            src="/static/img/icon-event.png"
          />
          <view class="text-group_80 flex-col">
            <text class="text_49">活动福利</text>
            <text class="text_50">Event&nbsp;Benefits</text>
          </view>
        </view>
      </view>

      <!-- 右侧市场行情区 -->
      <view class="box_11 flex-col">
        <!-- 盈利提示 -->
        <view class="image-text_64 flex-row justify-between">
          <image
            class="label_5"
            referrerpolicy="no-referrer"
            src="/static/img/icon-speaker.png"
          />
          <text class="text-group_4">
            用户137****1394在秒合约中盈利460&nbsp;USDT
          </text>
        </view>

        <!-- 大盘行情卡片 -->
        <view class="box_41 flex-row justify-between">
          <!-- BTC卡片 -->
          <view class="box_20 flex-col">
            <view class="image-text_65 flex-row justify-between">
              <image
                class="label_12"
                referrerpolicy="no-referrer"
                src="/static/img/coin-btc.png"
              />
              <view class="text-group_81 flex-col">
                <text class="text_36">BTC&nbsp;/&nbsp;USDT</text>
                <text class="text_35">Bitcoin</text>
              </view>
            </view>
            <view class="image-text_66 flex-row justify-between">
              <view class="text-group_82 flex-col">
                <text class="text_38">$108,283.26</text>
                <text class="text_37">-&nbsp;0.05%</text>
              </view>
              <image
                class="image_12"
                referrerpolicy="no-referrer"
                src="/static/img/chart-red-1.png"
              />
            </view>
          </view>

          <!-- ETH卡片 -->
          <view class="box_23 flex-col">
            <view class="image-text_67 flex-row justify-between">
              <view class="group_13 flex-col"></view>
              <view class="text-group_83 flex-col">
                <text class="text_40">ETH&nbsp;/&nbsp;USDT</text>
                <text class="text_39">Ethereum</text>
              </view>
            </view>
            <view class="text-group_84 flex-col">
              <text class="text_41">$4,322.91</text>
              <text class="text_42">+&nbsp;1.29%</text>
            </view>
          </view>
        </view>

        <!-- Hot标题 -->
        <view class="box_42 flex-row justify-between">
          <text class="text_11">Hot</text>
          <image
            class="label_11"
            referrerpolicy="no-referrer"
            src="/static/img/icon-fire.png"
          />
        </view>

        <!-- 热门币种列表 -->
        <!-- BTC/USDT -->
        <view class="box_43 flex-row justify-between">
          <view class="image-text_68 flex-row justify-between">
            <view class="box_12 flex-col"></view>
            <view class="text-group_85 flex-col">
              <text class="text_12">BTC/USDT</text>
              <text class="text_13">Bitcoin</text>
            </view>
          </view>
          <view class="image-text_69 flex-row justify-between">
            <image
              class="image_5"
              referrerpolicy="no-referrer"
              src="/static/img/chart-green-1.png"
            />
            <view class="text-group_86 flex-col">
              <text class="text-group_6">$108,283.26</text>
              <text class="text_14">+&nbsp;0.32%</text>
            </view>
          </view>
        </view>

        <!-- ETH/USDT -->
        <view class="box_44 flex-row">
          <view class="image-text_70 flex-row justify-between">
            <view class="box_13 flex-col"></view>
            <view class="text-group_87 flex-col">
              <text class="text_15">ETH/USDT</text>
              <text class="text_16">Ethereum</text>
            </view>
          </view>
          <image
            class="image_6"
            referrerpolicy="no-referrer"
            src="/static/img/chart-green-2.png"
          />
          <view class="text-group_88 flex-col">
            <text class="text_17">$343.01</text>
            <text class="text_18">+&nbsp;1.07%</text>
          </view>
        </view>

        <!-- SUI/USDT -->
        <view class="box_45 flex-row">
          <view class="image-text_71 flex-row justify-between">
            <view class="image-wrapper_2 flex-col">
              <image
                class="label_6"
                referrerpolicy="no-referrer"
                src="/static/img/coin-sui.png"
              />
            </view>
            <view class="text-group_89 flex-col">
              <text class="text_20">SUI/USDT</text>
              <text class="text_19">SuiCoin</text>
            </view>
          </view>
          <image
            class="image_7"
            referrerpolicy="no-referrer"
            src="/static/img/chart-green.png"
          />
          <view class="text-group_90 flex-col">
            <text class="text_21">$1,422.01</text>
            <text class="text_22">+&nbsp;12.06%</text>
          </view>
        </view>

        <!-- XRP/USDT -->
        <view class="box_46 flex-row">
          <view class="image-text_72 flex-row justify-between">
            <view class="block_6 flex-col"></view>
            <view class="text-group_91 flex-col">
              <text class="text_23">XRP/USDT</text>
              <text class="text_24">Ripple</text>
            </view>
          </view>
          <image
            class="image_8"
            referrerpolicy="no-referrer"
            src="/static/img/SketchPngc750d9e750354d07aab11cd14e5f678180de3e758e313ad25db47a83674b2bfc.png"
          />
          <view class="text-group_92 flex-col">
            <text class="text_25">$2.3817</text>
            <text class="text_26">-&nbsp;0.32%</text>
          </view>
        </view>

        <!-- SOLU/USDT -->
        <view class="box_47 flex-row">
          <view class="block_9 flex-col"></view>
          <view class="image-text_73 flex-row justify-between">
            <view class="text-group_93 flex-col">
              <text class="text_27">SOLU/USDT</text>
              <text class="text_28">Soul&nbsp;Scanner</text>
            </view>
            <image
              class="image_9"
              referrerpolicy="no-referrer"
              src="/static/img/chart-green-2.png"
            />
          </view>
          <view class="text-group_94 flex-col">
            <text class="text_29">$183.01</text>
            <text class="text_30">+&nbsp;1.07%</text>
          </view>
        </view>

        <!-- ADA/USDT -->
        <view class="box_48 flex-row">
          <view class="image-text_74 flex-row justify-between">
            <view class="block_17 flex-col">
              <view class="image-wrapper_3 flex-col">
                <image
                  class="label_7"
                  referrerpolicy="no-referrer"
                  src="/static/img/coin-ada.png"
                />
              </view>
            </view>
            <view class="text-group_95 flex-col">
              <text class="text_31">ADA/USDT</text>
              <text class="text_32">Ada&nbsp;Lovelace</text>
            </view>
          </view>
          <image
            class="image_10"
            referrerpolicy="no-referrer"
            src="/static/img/SketchPngce2a11d1eb5c8c2c202625a0cb209be00dcd9f7093a5728189576a5630d4ce7c.png"
          />
          <view class="text-group_96 flex-col">
            <text class="text_33">$0.6317</text>
            <text class="text_34">-&nbsp;12.06%</text>
          </view>
        </view>

        <image
          class="image_13"
          referrerpolicy="no-referrer"
          src="/static/img/home-indicator.png"
        />
      </view>
    </view>
    
    <!-- 底部导航栏组件 -->
    <custom-tabbar></custom-tabbar>
  </view>
</template>

<script>
export default {
  data() {
    return {
      constants: {},
    };
  },
  methods: {}
};
</script>

<style>
@import './common.css';
@import '@/custom/home.css';
</style>
