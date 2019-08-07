<template>
    <div>
        <button @click="Click()">
            <!--編集用モーダル出したい-->
            編集する
        </button>
        <EditAlbumComponent
            :album_id = album_id
            ref="modal"
        />
    </div>
</template>


<script>
    import EditAlbumComponent from './EditAlbumComponent.vue'
    export default {
        components: {
            EditAlbumComponent
        },
        props: {
            album_id: Number,
        },
        methods: {
            Click: function(){
                this.$refs.modal.openModal()
                this.$refs.modal.getInfo(album_id)
            }
        }
    }
</script>