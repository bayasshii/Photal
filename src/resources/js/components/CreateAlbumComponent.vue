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
                        <option v-for="app_user in app_users" v-bind:value="app_user.github_id" v-bind:key="app_user.github_id">
                            {{ app_user.github_id }}
                        </option>
                    </select>
                </label>
            </div>
            <div>
                <label>写真:
                    <input @change="selectedFile" ref="img_inp" type="file" multiple>
                </label>
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
                uploadFile: null
            };
        },
        methods: {
            selectedFile: function(e) {
                // 選択された File の情報を保存しておく
                e.preventDefault();
                let files = e.target.files;
                this.uploadFile = files;
                console.log(this.uploadFile)
            },
            // Dataを送信する
            postInfo: function() {
                axios.defaults.withCredentials = true;
                var album_id = Math.floor( Math.random() * 1000000 );
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
                axios.post('api/photal/',albumData)
                Promise.all(array).then(
                    function(){
                        this.$emit('callParent')
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
                })
            }
        },
        created() {
            // APIアクセスでgetInfo()を呼び出す
            this.getInfo()
        }
    }
</script>