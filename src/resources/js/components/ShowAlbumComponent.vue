<!-- Albumの情報取得してループで表示する -->

<template>
    <div>
        <!--

            **********************
            送信の仕方
            めもだよ
            **********************

            <div>
                イェーイ。<br>
                {{ album_id }}<br>
                イェー！
                <label>姓
                    <input type="text" v-model="lastName">
                </label>
                <button class="button" type="text" @click="onSubmit()">送信する</button>
            </div>
        -->
        <div v-for="a in albums">
            <div class="album__container">
                ひどうきなんだな
                <a :href="'/photal/detail/' + a.album_id"><h2>{{a.album_name}}</h2></a>
                <!--メンバー表示-->
                <div class="album__membersContainer flex">
                    <div v-for="am in album_members">
                        <div v-if="am.album_id == a.album_id">
                            <div class="album__mambersContainer--item">
                                <a>{{am.album_member}}</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!--写真表示-->
                <div class="album__imgsContainer">
                    <div v-for="ap in album_photos">
                        <div v-if="ap.album_id == a.album_id">
                            <div class="album__imgsContainer--item">
                                {{ap.album_photo_id}}
                                <img :src="'https://bayashi.s3-ap-northeast-1.amazonaws.com/' + ap.album_photo_id">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() { 
        //データの初期値を設定
        return {
            album_id: "",
            albums:"",
            album_members: "", 
            album_photos: ""
        };
    },
    methods: {
        getInfo: function() {
            var self = this;
            axios.get('photal/api')
                .then(res =>  {
                    self.albums = res.data.albums;
                    self.album_members = res.data.album_members;
                    self.album_photos = res.data.album_photos;
                }).catch( error => { console.log(error); });
        }
    },
    created() {
        //APIアクセスでgetInfo()を呼び出す
        this.getInfo()
    }}
</script>