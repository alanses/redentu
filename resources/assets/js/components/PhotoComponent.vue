<template>
    <div>
        <div v-if="this.status.changeSuccess" class="image">
            <img :src="'result/' + responseImage">
        </div>
        <form v-on:submit.prevent="sendImageToServer" enctype="multipart/form-data">
            <div class="form-group">
                <label>Виберіть основне зображення</label>
                <input type="file"
                       @change="onFileChange"
                       class="form-control"
                       id="exampleInputEmail1"
                       aria-describedby="emailHelp"
                       placeholder="Забраження"
                       name="image"
                >
            </div>
            <div class="form-group">
                <label>Введіть текст який буде в якості тексту на картинці</label>
                <input type="text"
                       v-model="textForWaterMark"
                       class="form-control"
                >
            </div>
            <div class="form-group">
                <label>Виберіть параметри обрізки по ширені в px</label>
                <input type="text"
                       class="form-control"
                       placeholder="По ширені"
                       v-model="changeImageParam.weight"
                >
            </div>

            <div class="form-group">
                <label>Виберіть параметри обрізки по висоті в px</label>
                <input type="text"
                       class="form-control"
                       placeholder="По висоті"
                       v-model="changeImageParam.height"
                >
            </div>
            <div class="dropzone">
                <h2>Виберіть зображення яке буде використане як водяний знак</h2>
                <vue-dropzone
                        ref="myVueDropzone"
                        id="dropzone"
                        :options="dropzoneOptions"
                        @vdropzone-file-added="(file) => getImageForWatermark(file)"
                ></vue-dropzone>
            </div>
            <button
                    type="submit"
                    class="btn btn-primary"
            >Відправити
            </button>
            <img src=""/>
        </form>
    </div>
</template>

<script>
    import vue2Dropzone from 'vue2-dropzone'
    import 'vue2-dropzone/dist/vue2Dropzone.min.css'

    export default {
        components: {
            vueDropzone: vue2Dropzone,
        },
        data() {
            return {
                dropzoneOptions: {
                    url: '/save-image',
                    thumbnailWidth: 150,
                    acceptedFiles: '.jpg,.jpeg,.png',
                    maxFilesize: 2,
                    maxFiles: 1,
                    chunking: true,
                    chunkSize: 500,
                    autoProcessQueue: false
                },
                transferObject: new FormData(),
                textForWaterMark: '',
                status: {
                    changeSuccess: false,
                    changeErrors: false
                },
                changeImageParam: {
                    weight: '',
                    height: ''
                },
                responseImage: ''
            }
        },
        methods: {
            onFileChange(e) {
                let files = e.target.files || e.dataTransfer.files
                if (!files.length)
                    return
                this.transferObject.append('mainImage', files[0], files[0].name)
            },

            sendImageToServer() {
                this.$http.post('/save-image-as-watermark', this.assembleInfoForServer()).then(res => {
                    if (res.status === 201) {
                        this.responseImage = res.data.imageInfo.basename
                        this.status.changeSuccess = true
                        this.$forceUpdate();
                    }
                })
            },

            assembleInfoForServer(){
                this.transferObject.append('textForWaterMark', this.textForWaterMark)
                this.transferObject.append('weight', this.changeImageParam.weight)
                this.transferObject.append('height', this.changeImageParam.height)

                return this.transferObject;
            },

            getImageForWatermark(file) {
                this.transferObject.append('watermark', file)
            },

            getImageFromServer(data) {
                console.log(data)
            }
        }
    }
</script>

<style scoped>

</style>