<!-- Albumの情報をAPIに送信する -->
<template>
    <div>
        <div class="hoge">
            <div>
                <label>アルバム名:
                    <input type="text" v-model="AlbumName">
                </label>
            </div>
            <div>
                <label>ともだち:
                    <select v-model="AlbumMembersSelected" multiple>
                        <option disabled value="">選択してね〜</option>
                        <option 
                            v-for="app_user in app_users" 
                            v-bind:value="app_user.github_id" 
                            v-bind:key="app_user.github_id"
                            v-if="app_user.github_id != github_user.nickname"
                        >
                            {{ app_user.github_id }}
                        </option>
                    </select>
                    <div class="flex">
                        <div class="album__mambersContainer--item">
                        {{github_user.nickname}}
                        </div>
                        <div
                            v-for="members in AlbumMembersSelected"
                            v-bind:key="members"
                            class="album__mambersContainer--item"
                        >
                            {{members}}
                        </div>
                    </div>
                </label>
            </div>
            <div class="photo_input">
                <label>写真を選んでね
                    <input @change="selectedFile" ref="img_inp" type="file" accept="image/*" multiple>
                </label>
                <div v-if="imageData.length">
                    <div>プレビュー</div>
                    <div class="album__imgsContainer flex">
                        <div
                            v-for="image in imageData"
                            v-bind:key="image"
                            class="album__imgsContainer--item"
                        >
                            <img :src="image" v-if="imageData">
                        </div>
                    </div>
                </div>
            </div>
            <button class="button" @click="postInfo()">送信する</button>
        </div>
    </div>
</template>
<script>
    export default {
        data() { 
            //データの初期値を設定
            return {
                album_name:"",
                github_user:"",
                app_users:[],
                AlbumName:"",
                album_members_selected: [],
                AlbumMembersSelected: [],
                album_photos: [],
                AlbumPhotos: [],
                uploadFile: null,
                AlbumMembersSelected: [],
                imageData:[]
            };
        },
        methods: {
            selectedFile: function(e) {
                // 選択された File の情報を保存しておく
                e.preventDefault();
                let files = e.target.files;
                this.uploadFile = files;

                // プレビュー表示するためにrender系の色々する
                if(this.uploadFile.length > 0) {
                    for (var i=0; i < this.uploadFile.length; i++){
                        var file = this.uploadFile[i]
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.imageData.push(e.target.result)
                        };
                        reader.readAsDataURL(file);
                    }
                }
            },
            // Dataを送信する
            postInfo: function() {
                axios.defaults.withCredentials = true;
                var album_id = Math.floor( Math.random() * 100000000 );

                // メンバーに自分も追加
                this.AlbumMembersSelected.push(this.github_user.nickname)

                // 送信するためにまとめてjsonに入れる
                var albumData = {
                    album_name: this.AlbumName,
                    album_members_selected: this.AlbumMembersSelected,
                    album_photos: this.AlbumPhotos,
                    album_id: album_id,
                    xsrfCookieName: "XSRF-TOKEN",
                    withCredentials: true
                }
                let config = {
                    headers: {
                        'content-type': 'multipart/form-data'
                    }
                };

                // 良い感じに画像送信するためのあれこれ
                const api = axios.create();
                var array = []
                for (var i = 0; i < this.uploadFile.length; i++) {
                    let formData = new FormData();
                    formData.append('image', this.uploadFile[i]);
                    formData.append('album_id', album_id);
                    array.push(api
                        .post('api/photalTest', formData, config)
                    )
                }

                // 画像以外のデータの送信
                axios.post('api/photal/',albumData)
                // 再読み込み
                Promise.all(array).then(
                    function(){
                        this.$emit('callParent')
                        // フォームの初期化
                        this.uploadFile = null
                        this.AlbumName = ""
                        this.AlbumMembersSelected =[]
                        this.imageData=[]
                    }.bind(this)
                )
            },

            // 情報取得
            getInfo: function() {
                var self = this;
                axios.get('api/photal')
                .then(res =>  {
                    self.albums = res.data.albums;
                    self.album_members = res.data.album_members;
                    self.album_photos = res.data.album_photos;
                    self.app_users = res.data.app_users;
                    self.github_user = res.data.github_user
                })
            }
        },
        created() {
            // APIアクセスでgetInfo()を呼び出す
            this.getInfo()
        }
    }
</script>