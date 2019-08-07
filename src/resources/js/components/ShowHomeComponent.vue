<template>
    <div>
        データが取れてない
        {{albums}}
        <div v-for="a in albums">
            <div class="album__container">
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
                <!--アルバム写真追加機能-->
                <div>
                    <label>写真追加:
                        <input @change="selectedFile" ref="file" type="file" multiple>
                    </label>
                    <button class="button" @click="upload(a.album_id)">送信する</button>
                </div>
                <!--アルバム削除機能-->
                <form method="post">
                    <button type="submit" class="btn">DELETE</button>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() { 
            // データの初期値を設定
            return {
                albums:"",
                album_members: "", 
                album_photos: "",
                AlbumPhotos:"",
                uploadFile: null
            };
        },

        methods: {
            // 情報取得
            getInfo: function() {
                var self = this;
                axios.get('/api/photal/home')
                .then(res =>  {
                    self.albums = res.data.albums;
                    self.album_members = res.data.album_members;
                    self.album_photos = res.data.album_photos;
                })
            },
            //　画像追加
            // input
            selectedFile: function(e) {
                // 選択された File の情報を保存しておく
                let files = e.target.files || e.dataTransfer.files
                this.uploadFile = files;
            },
            // submit
            upload: function(album_id) {
                axios.defaults.withCredentials = true;
                var data = {
                    album_files: this.uploadFile,
                    album_id: album_id, 
                    xsrfCookieName: "XSRF-TOKEN",
                    ithCredentials: true
                }
                axios
                .post('/api/photal/put', data)
                .then(response => {
                    console.info(data)
                })
            }
        },
        created() {
            // APIアクセスでgetInfo()を呼び出す
            this.getInfo()
        }
    }
</script>