<!-- Albumの情報をAPIに送信する -->
<template>
    <div>
        <div class="creatAlbum__wrap">
            <div class="createAlbum--title">新規アルバム作成</div>
            <div>
                <label>
                    <input class="albumName--input w-100" type="text" v-model="AlbumName" placeholder="アルバム名を入力してね">
                </label>
            </div>
            <div class="selecteFriend">
                <div class="title">ともだちを選択してね</div>
                <div class="title mb-20">現在、{{AlbumMembersSelected.length+1}}人 選択されています</div>
                <label class="flex textcenter">
                    <div class="flex">
                        <div class="memberSelected flex">
                            <img
                                class="profile--img profile--img--mini"
                                :src="'https://github.com/'+github_user.nickname+'.png'"
                            />
                            <span class="pl-5">{{github_user.nickname}}</span>
                        </div>
                    </div>
                    <div
                        v-for="app_user in app_users" 
                        v-if="app_user.github_id != github_user.nickname"
                        @click="addMember(app_user.github_id)"
                    >
                        <div
                            v-if="AlbumMembersSelected.some( function( value ) {return value === app_user.github_id})"
                            class="memberSelected flex"
                        >
                            <img
                                class="profile--img profile--img--mini"
                                :src="'https://github.com/'+app_user.github_id+'.png'"
                            />
                            <span class="pl-5">{{ app_user.github_id }}</span>
                        </div>
                        <div
                            v-else
                            class="memberNotSelected flex"
                        >
                            <img
                                class="profile--img profile--img--mini"
                                :src="'https://github.com/'+app_user.github_id+'.png'"
                            />
                            <span class="pl-5">{{ app_user.github_id }}</span>
                        </div>

                    </div>
                </label>
            </div>
            <div class="photo_input">
                <label class="albumImage--input">
                    写真を追加してね
                    <input @change="selectedFile" ref="img_inp" type="file" accept="image/*" multiple>
                </label>
                <div class="pt-10">現在、{{imageData.length}}枚 選択されています</div>
            </div>
            <button class="button creatAlbum--submit" @click="postInfo()" v-bind:disabled="isPush">送信する</button>
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
                imageData:[],
                isPush : false
            };
        },
        methods: {
            addMember(member_id){
                if(this.AlbumMembersSelected.some( function( value ) {
                    return value === member_id})
                    ){
                    this.AlbumMembersSelected = this.AlbumMembersSelected.filter(function(a) {
                    return a !== member_id;
                    });
                }
                else{
                    this.AlbumMembersSelected.push(member_id)
                }
            }
            ,
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
            }
            ,
            // Dataを送信する
            postInfo: function() {
                this.pushBtn();
                axios.defaults.withCredentials = true;
                var album_id = Math.floor( Math.random() * 100000000 );

                // 送信するためにまとめてjsonに入れる
                var albumData = {
                    nickname: this.github_user.nickname,
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
                        .post('/api/photalTest', formData, config)
                    )
                }

                // 画像以外のデータの送信
                axios.post('/api/photal/',albumData)
                // 再読み込み
                Promise.all(array).then(
                    function(){
                        this.$emit('callParent')
                        // フォームの初期化
                        this.uploadFile = null
                        this.AlbumName = ""
                        this.AlbumMembersSelected =[]
                        this.imageData=[]
                        this.resetBtn()
                    }.bind(this)
                )
            },
            // 情報取得
            getInfo: function() {
                var self = this;
                axios.get('/api/photal/github')
                .then(res =>  {
                    self.app_users = res.data.app_users;
                    self.github_user = res.data.github_user
                })
            }
            ,
            pushBtn: function() {
                this.isPush = true;
            }
            ,
            resetBtn: function(){
                this.isPush = false;
            }
        },
        created() {
            // APIアクセスでgetInfo()を呼び出す
            this.getInfo()
        }
    }
</script>