<template>
  <div class="page flex-col">
    <div class="box_1 flex-col">
      <div class="section_1 flex-row">
        <div class="text-wrapper_1 flex-col"><span class="text_1">9:4</span></div>
        <img
          class="thumbnail_1"
          referrerpolicy="no-referrer"
          src="./assets/img/SketchPngf65c95bd53e8865c2739da431d0fcbd8f8d32b922b0075a874e28852620f4234.png"
        />
        <img
          class="thumbnail_2"
          referrerpolicy="no-referrer"
          src="./assets/img/SketchPng860e9ae07aac3ce17f06fffc6efeea5d79120a31892c10419baab39bb3c12905.png"
        />
        <img
          class="image_1"
          referrerpolicy="no-referrer"
          src="./assets/img/SketchPng6db081d5b6892e112c0ea07da6a61a7538ab31b290452a4d3692801cb40e06dd.png"
        />
      </div>
    </div>
    <div class="section_8 flex-col">
      <div class="block_16 flex-row justify-between">
        <img
          class="label_7"
          referrerpolicy="no-referrer"
          src="./assets/img/SketchPngb8a7a420038787ed85d02e7305442b9a3a7ff9b831f7a0a0eec62fc160cfb856.png"
        />
        <div class="text-wrapper_25 flex-col">
          <span class="text_47">Bitcoin</span>
          <span class="text_48">BTC</span>
        </div>
        <img
          class="label_8"
          referrerpolicy="no-referrer"
          src="./assets/img/SketchPng7c4ca7871bec05aef10ab789859c47571e86fb235ba7f817631dea42951c7c0d.png"
        />
      </div>
      <div class="block_17 flex-row justify-between">
        <div class="group_16 flex-row justify-between">
          <img
            class="label_9"
            referrerpolicy="no-referrer"
            src="./assets/img/SketchPnge9d19cd3f0ad73081e971951fab60d401af8af65e65f8af418f3785898987181.png"
          />
          <div class="box_25 flex-row">
            <div class="text-group_33 flex-col">
              <span class="text_49">账户余额</span>
              <span class="text_50">$234.11</span>
            </div>
          </div>
        </div>
        <div class="group_17 flex-row justify-between">
          <img
            class="label_10"
            referrerpolicy="no-referrer"
            src="./assets/img/SketchPng3e5ca2fcd3b8a516b94574c8ff12930bb47632f005503e60d72e141b9989ba49.png"
          />
          <div class="group_18 flex-row">
            <div class="text-group_34 flex-col">
              <span class="text_51">模拟交易</span>
              <span class="text_52">$34.11</span>
            </div>
          </div>
        </div>
      </div>
      <div class="block_18 flex-row justify-between">
        <div class="group_20 flex-row justify-between">
          <div class="box_32 flex-col">
            <div class="box_27 flex-row">
              <div class="image-text_26 flex-row justify-between">
                <img
                  class="label_11"
                  referrerpolicy="no-referrer"
                  src="./assets/img/b4f1cace997e49fdb1c2a82bd02a2983_mergeImage.png"
                />
                <span class="text-group_28">BTC/USDT</span>
              </div>
            </div>
            <span class="text_53">$112,85.00</span>
          </div>
          <div class="box_28 flex-row">
            <div class="image-text_27 flex-row justify-between">
              <img
                class="thumbnail_5"
                referrerpolicy="no-referrer"
                src="./assets/img/SketchPng9e0f353e3a292a18b98973d99adf37f2b0b455311fab4c3a7e7af3956c00677a.png"
              />
              <span class="text-group_29">+4,08%</span>
            </div>
          </div>
        </div>
        <img
          class="label_12"
          referrerpolicy="no-referrer"
          src="./assets/img/SketchPngcfaa554b9bab7631f84a24fc2fbb6d6265287736689b48649414244be9f41e50.png"
        />
      </div>
    </div>
    <div class="box_7 flex-col">
      <div class="box_8 flex-col">
        <div class="group_4 flex-row justify-between">
          <div class="text-wrapper_3 flex-col"><span class="text_9">24H</span></div>
          <div class="list_6 flex-row">
            <div class="text-wrapper_4 flex-col" v-for="(item, index) in loopData0" :key="index">
              <span class="text_10" v-html="item.lanhutext0"></span>
            </div>
          </div>
          <div class="text-wrapper_5 flex-col"><span class="text_11">ALL</span></div>
        </div>
      </div>
      <div class="box_9 flex-col">
        <img
          class="image_2"
          referrerpolicy="no-referrer"
          src="./assets/img/SketchPng86f960065626bb92cec93e5a9985a5e8abfba287467405ee4d0c8d1c0da02c04.png"
        />
        <div class="block_6 flex-row justify-between">
          <span class="text_12">01.00</span>
          <span class="text_13">02.00</span>
          <div class="text-wrapper_6 flex-col"><span class="text_14">03.00</span></div>
          <span class="text_15">04.00</span>
          <span class="text_16">05.00</span>
          <span class="text_17">06.00</span>
          <span class="text_18">07.00</span>
        </div>
        <div class="block_7 flex-col">
          <div class="text-wrapper_7 flex-col"><span class="text_19">$1090.00</span></div>
          <img
            class="image_3"
            referrerpolicy="no-referrer"
            src="./assets/img/SketchPngb93c13a45a337a1922456fdadea09cc08156c7bbc279c30fe63690d1580873c7.png"
          />
        </div>
      </div>
    </div>
    <div class="section_9 flex-col">
      <div class="block_8 flex-row justify-between">
        <div class="text-wrapper_28 flex-col">
          <span class="text_20">当前委托订单</span>
          <span class="text_21">3</span>
        </div>
        <div class="group_21 flex-row justify-between">
          <div class="image-text_28 flex-col">
            <div class="box_12 flex-col">
              <div class="text-wrapper_9 flex-col"><span class="text_22">00:59</span></div>
            </div>
            <span class="text-group_5">Sec</span>
          </div>
          <div class="image-text_29 flex-col">
            <div class="block_9 flex-col">
              <img
                class="thumbnail_4"
                referrerpolicy="no-referrer"
                src="./assets/img/SketchPng0627b034eabb1e90c8880f43f39d0668acfabf3e7cf8a91637f94d9df0e1a367.png"
              />
              <span class="text_23">00:14</span>
            </div>
            <span class="text-group_6">Sec</span>
          </div>
          <div class="image-text_30 flex-col">
            <div class="text-wrapper_10 flex-col"><span class="text_24">00:01</span></div>
            <span class="text-group_7">Sec</span>
          </div>
        </div>
      </div>
      <div class="text-wrapper_29 flex-row justify-between">
        <span class="text_25">Time</span>
        <span class="text_26">Amount</span>
      </div>
      <div class="grid_11 flex-row">
        <div class="box_13 flex-row">
          <div class="text-wrapper_12 flex-col">
            <span class="text_27">30S</span>
            <span class="text_28">80%</span>
          </div>
          <div class="text-group_35 flex-col">
            <span class="text_29">M1</span>
            <span class="text_30">81%</span>
          </div>
          <div class="text-group_36 flex-col">
            <span class="text_31">M2</span>
            <span class="text_32">82%</span>
          </div>
          <div class="text-group_37 flex-col">
            <span class="text_33">M3</span>
            <span class="text_34">83%</span>
          </div>
        </div>
        <div class="box_14 flex-row justify-between">
          <div class="text-wrapper_13 flex-col"><span class="text_35">$10</span></div>
          <span class="text_36">$30</span>
          <span class="text_37">$50</span>
          <span class="text_38">$100</span>
        </div>
        <div class="text-wrapper_14 flex-row justify-between">
          <span class="text_39">Time</span>
          <span class="text_40">30S</span>
        </div>
        <div class="text-wrapper_15 flex-row justify-between">
          <span class="text_41">Amount</span>
          <span class="text_42">$10</span>
        </div>
      </div>
      <div class="block_10 flex-col">
        <div class="image-wrapper_7 flex-row">
          <img
            class="image_4"
            referrerpolicy="no-referrer"
            src="./assets/img/SketchPng26334c3f9a4acf3ad1e17643c5d9b6350fc5e434a8ad4bc51376a43d7b335cb4.png"
          />
        </div>
        <div class="block_19 flex-row justify-between">
          <div class="grid_12 flex-row">
            <div class="text-wrapper_16 flex-col" v-for="(item, index) in loopData1" :key="index">
              <span class="text_43" v-html="item.lanhutext0"></span>
            </div>
          </div>
          <div class="box_33 flex-col">
            <div class="image-wrapper_2 flex-col">
              <img
                class="image_5"
                referrerpolicy="no-referrer"
                src="./assets/img/SketchPng5890667b8adb0ff896a383babe5d0f85d4583a78fc746ed30c9194e885b54e99.png"
              />
            </div>
            <div class="text-wrapper_17 flex-col"><span class="text_44">确认</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  data() {
    return {
      loopData0: [{ lanhutext0: '1W' }, { lanhutext0: '1M' }, { lanhutext0: '1Y' }],
      loopData1: [
        { lanhutext0: '0' },
        { lanhutext0: '1' },
        { lanhutext0: '2' },
        { lanhutext0: '3' },
        { lanhutext0: '4' },
        { lanhutext0: '5' },
        { lanhutext0: '6' },
        { lanhutext0: '7' },
        { lanhutext0: '8' },
        { lanhutext0: '9' }
      ],
      constants: {}
    };
  },
  methods: {}
};
</script>
<style scoped lang="css" src="./assets/index.css" />