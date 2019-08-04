<template>
    <div>
        <div>
            イェーイ。<br>
            {{ album_id }}<br>
            イェー！
            <label>姓
                <input type="text" v-model="lastName">
            </label>
            <button class="button" type="text" @click="onSubmit()">送信する</button>
        </div>
        <div v-for="a in albums">
            <div class="album__container">
                <a><h2>{{a.album_name}}</h2></a>
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
    data() { //データの初期値を設定
        return {
            album_id: "",
            last_name:"",
            lastName:"",
            albums:"",
            album_members: "", 
            album_photos: ""
        };
    },
    methods: {
        onSubmit: function() {
            // if(!confirm('送信します。よろしいですか？')) {return;}
            let data = {
                last_name: this.lastName,
            };
            var self = this;
            axios.post('photal/api', data)
                .then(res =>  {
                    self.last_name = res.data.lastName;
                    self.albums = res.data.albums;
                    self.album_members = res.data.album_members;
                    self.album_photos = res.data.album_photos;
                }).catch( error => { console.log(error); });
        },
        getProfiles() {
        const data = {
            album_id: '621881778' //今回投げるuserid
        }
        var self = this;
        axios.post('photal/api', data)
        .then(res =>  {
            self.album_id = res.data.album_id;
        }).catch( error => { console.log(error); });
        }
    },
    created() { //APIアクセスでgetProfiles()を呼び出す
        this.getProfiles()
        this.onSubmit()
    }}
</script>