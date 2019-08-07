<template>
    <div>
        <button @click="Click(album_id)">
            <!--編集用モーダル出したい-->
            編集する
        </button>
        <EditAlbumComponent
            ref="modal"
            @update="update"
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
            Click: function(album_id){
                this.$refs.modal.openModal(album_id)
            },
            update:function(){
                this.$emit('getInfo')
            }
        }
    }
</script>