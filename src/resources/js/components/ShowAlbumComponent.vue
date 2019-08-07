<template>
    <div>
        <div 
            v-for="a in albums"
            v-bind:key="a.id"
            class="album__container"
        >
            <a :href="'/photal/detail/' + a.album_id"><h2>{{a.album_name}}</h2></a>
            <!--メンバー表示-->
            <div class="album__membersContainer flex">
                <div 
                    v-for="am in album_members"
                    v-bind:key="am.id"
                    v-if="am.album_id == a.album_id"
                    class="album__mambersContainer--item"
                >
                    <a>{{am.album_member}}</a>
                </div>
            </div>
            <!--写真表示-->
            <div class="album__imgsContainer flex">
                <div
                    v-for="album_photo in album_photos"
                    v-bind:key="album_photo.id"
                    v-if="album_photo.album_id == a.album_id"
                    class="album__imgsContainer--item"
                >
                    <img :src="'https://bayashi.s3-ap-northeast-1.amazonaws.com/' + album_photo.album_photo_id">
                    <div>
                        <button class="button" @click="loveBtn(album_photo.album_photo_id, github_user.id)">
                            <div
                                v-if="your_love_photos.filter(x => x.album_photo_id === album_photo.album_photo_id).length"
                            >
                                ダークハート！！
                            </div>
                            <div v-else>
                                ハート
                            </div>
                        </button>
                        <div 
                            v-for="love_count in love_counts"
                            v-bind:key="love_count.id"
                            v-if="album_photo.album_photo_id == love_count.album_photo_id"
                        >
                            {{love_count.love_count}}
                        </div>
                    </div>
                </div>
            </div>

            <!--メニュー-->
            <div
                v-for="am in album_members"
                v-bind:key="am.id"
                v-if="am.album_id == a.album_id"
            >
                <div
                    v-if="am.album_member == github_user.nickname"
                    class="albumMenu"
                >
                    <AlbumMenuComponent
                        :album_id = a.album_id
                        @callParent = "getInfo()"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import AlbumMenuComponent from './AlbumMenuComponent.vue'
    export default {
        components: {
            AlbumMenuComponent
        },
        data() { 
            //データの初期値を設定
            return {
                albums:"",
                album_members: [], 
                album_photos: "",
                AlbumPhotos:"",
                uploadFile: null,
                love_counts: "",
                github_user:"",
                your_love_photos:"",
            };
        },
        methods: {
            // 情報取得
            getInfo: function() {
                var self = this;
                axios.get('api/photal')
                .then(res =>  {
                    self.albums = res.data.albums;
                    self.album_members = res.data.album_members;
                    self.album_photos = res.data.album_photos;
                    self.love_counts = res.data.love_counts;
                    self.github_user = res.data.github_user;
                    self.your_love_photos = res.data.your_love_photos;
                })
            },
            // 良いねボタン
            loveBtn: function(albumPhotoId,githubId) {
                var data = {
                    album_photo_id: albumPhotoId,
                    github_id: githubId
                }
                axios.post('api/photal/love', data)
                .then(res =>  {
                    this.love_counts = res.data.love_counts;
                    this.your_love_photos = res.data.your_love_photos;
                    console.log(this.love_counts)
                    console.log("受信完了")
                })
            },
            //　画像追加
            selectedFile: function(e) {
                e.preventDefault();
                let files = e.target.files;
                this.uploadFile = files;
            },
            upload: function(album_id) {
                let config = {
                    headers: {
                        'content-type': 'multipart/form-data'
                    }
                };
                let formData = new FormData();
                for (var i = 0; i < this.uploadFile.length; i++) {
                    let formData = new FormData();
                    formData.append('image', this.uploadFile[i]);
                    formData.append('album_id', album_id);
                    axios
                        .post('api/photalTest', formData, config)
                        .then(
                            // 再読み込みの仕方微妙、、、
                            this.getInfo()
                    )
                };
            },
            // 新規投稿された時読み込みさせる
            toChild: function() {
                this.getInfo();
            }
        },
        created() {
            // APIアクセスでgetInfo()を呼び出す
            this.getInfo();
        }
    }
</script>