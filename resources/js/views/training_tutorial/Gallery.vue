<template>
  <div class="flex column">
    <div id="_wrapper" class="pa-5">
      <v-overlay :absolute="absolute" :value="overlay">
        <v-progress-circular
          :size="70"
          :width="7"
          color="primary"
          indeterminate
        ></v-progress-circular>
      </v-overlay>
      <v-main>
        <v-breadcrumbs :items="items">
          <template v-slot:item="{ item }">
            <v-breadcrumbs-item :to="item.link" :disabled="item.disabled">
              {{ item.text.toUpperCase() }}
            </v-breadcrumbs-item>
          </template>
        </v-breadcrumbs>
        <div class="d-flex justify-content-end mb-3">
          <div>
            <v-menu offset-y>
              <template v-slot:activator="{ on, attrs }">
                <v-btn small v-bind="attrs" v-on="on" color="primary">
                  Actions
                  <v-icon small> mdi-menu-down </v-icon>
                </v-btn>
              </template>
              <v-list class="pa-1">
                <v-list-item
                  class="ma-0 pa-0"
                  style="min-height: 25px"
                  v-if="userPermissions.employee_list_import"
                >
                  <v-list-item-title>
                    <v-btn
                      color="primary"
                      class="ma-2"
                      width="120px"
                      small
                      @click="uploadFile()"
                    >
                      <v-icon class="mr-1" small> mdi-import </v-icon>
                      Upload
                    </v-btn>
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
          </div>
        </div>
        <v-card>
          <v-card-title class="mb-0 pb-0">
            Video Tutorials
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              hide-details=""
              single-line
            ></v-text-field>
            <v-spacer></v-spacer>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="">
            <v-row>
              <template v-for="(item, index) in videos">
                <v-col cols="3">
                  <v-card>
                    <v-img
                      src="/img/film-colored.png"
                      class="ml-4 mr-4"
                    ></v-img>

                    <v-card-title class="justify-center">
                      {{ item.title }}
                    </v-card-title>
                    <v-card-actions class="justify-center">
                      <v-btn
                        rounded
                        class="primary mb-2 pa-4"
                        @click="playVideo(item)"
                      >
                        <v-icon class="mr-1">mdi-play-circle</v-icon> Play
                        Video</v-btn
                      >
                    </v-card-actions>
                  </v-card>
                </v-col>
              </template>
            </v-row>
          </v-card-text>
        </v-card>
        <v-dialog v-model="dialog_upload" max-width="500px" persistent>
          <v-card>
            <v-card-title class="mb-0 pb-0">
              <span class="headline">Upload Video</span>
            </v-card-title>
            <v-divider></v-divider>
            <v-card-text>
              <v-container>
                <v-row>
                  <v-col class="mt-0 mb-0 pt-0 pb-0">
                    <v-file-input
                      v-model="file"
                      show-size
                      label="File input"
                      prepend-icon="mdi-paperclip"
                      required
                      :error-messages="fileErrors"
                      @change="$v.file.$touch() + (fileIsEmpty = false)"
                      @blur="$v.file.$touch()"
                    >
                      <template v-slot:selection="{ text }">
                        <v-chip small label color="primary">
                          {{ text }}
                        </v-chip>
                      </template>
                    </v-file-input>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col class="mt-0 mb-0 pt-0 pb-0">
                    <v-text-field
                      name="title"
                      v-model="title"
                      :error-messages="titleErrors"
                      label="Video Title"
                      @input="$v.title.$touch()"
                      @blur="$v.title.$touch()"
                    ></v-text-field>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col class="mt-0 mb-0 pt-0 pb-0">
                    <v-textarea
                      rows="3"
                      label="Description"
                      v-model="description"
                    ></v-textarea>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col>
                    <v-autocomplete
                      v-model="permitted_positions"
                      :items="positions"
                      item-text="name"
                      item-value="id"
                      label="Permitted Positions"
                      multiple
                      chips
                      :error-messages="permittedPositionsErrors"
                      @input="$v.permitted_positions.$touch()"
                      @blur="$v.permitted_positions.$touch()"
                    >
                      <template v-slot:selection="data">
                        <v-chip
                          color="secondary"
                          v-bind="data.attrs"
                          :input-value="data.selected"
                          @click="data.select"
                        >
                          {{ data.item.name }}
                        </v-chip>
                      </template>
                    </v-autocomplete>
                  </v-col>
                </v-row>
                <v-row
                  class="fill-height"
                  align-content="center"
                  justify="center"
                  v-if="uploading"
                >
                  <v-col class="subtitle-1 text-center" cols="12">
                    Uploading...
                  </v-col>
                  <v-col cols="6">
                    <v-progress-linear
                      color="primary"
                      indeterminate
                      rounded
                      height="6"
                    ></v-progress-linear>
                  </v-col>
                </v-row>
              </v-container>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn
                color="#E0E0E0"
                @click="(dialog_upload = false) + clear()"
                class="mb-4"
              >
                Cancel
              </v-btn>
              <v-btn
                color="primary"
                class="mb-4 mr-4"
                @click="save()"
                :disabled="uploadDisabled"
              >
                Upload
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
        <v-dialog v-model="dialog_video" max-width="1050px" persistent>
          <v-card>
            <v-card-title class="secondary white--text">
              <span class="headline">{{ video_title }}</span>
              <v-spacer></v-spacer>
              <v-icon @click="closeVideo()" class="white--text">
                mdi-close-circle
              </v-icon>
            </v-card-title>
            <v-card-text class="mt-4">
              <v-row>
                <v-col class="text-center">
                  <Media
                    :kind="'video'"
                    :isMuted="false"
                    :src="[video_src]"
                    poster="/img/video-play.jpg"
                    :autoplay="autoplay"
                    :controls="true"
                    :ref="'fish'"
                    :style="{ width: '1000px' }"
                  >
                  </Media>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-dialog>
      </v-main>
    </div>
  </div>
</template>
<script>
import Media from "@dongido/vue-viaudio";
import axios from "axios";
import { validationMixin } from "vuelidate";
import { required, maxLength, email } from "vuelidate/lib/validators";
import { mapState } from "vuex";
export default {
  mixins: [validationMixin],
  components: {
    Media,
  },
  validations: {
    file: { required },
    title: { required },
    permitted_positions: { required },
  },
  data() {
    return {
      absolute: true,
      overlay: false,
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Videos",
          disabled: true,
        },
      ],
      video: {
        sources: [
          {
            src: "/videos/dontspeak.mp4",
            type: "video/mp4",
          },
        ],
        errorMsg: "ERROR!",
      },
      file: [],
      fileIsEmpty: false,
      fileIsInvalid: false,
      uploadDisabled: false,
      uploading: false,
      dialog_upload: false,
      dialog_video: false,
      search: "",
      autoplay: false,
      title: "",
      description: "",
      videos: [],
      positions: [],
      permitted_positions: "",
      video_src: "",
      video_title: "",
    };
  },
  methods: {
    getVideos() {
      axios.get("api/training/videos").then(
        (response) => {
          this.videos = response.data.videos;
          this.positions = response.data.positions;
          
        },
        (error) => {
          this.isUnauthorized(error);
          console.log(error);
        }
      );
    },

    uploadFile() {
      this.dialog_upload = true;
      this.file = [];
      this.$v.$reset();
    },
    save() {
      this.$v.$touch();
      this.fileIsEmpty = false;
      this.fileIsInvalid = false;

      if (!this.$v.file.$error) {
        this.uploadDisabled = true;
        this.uploading = true;
        let formData = new FormData();

        formData.append("file", this.file);
        formData.append("title", this.title);
        formData.append("description", this.description);
        formData.append("permitted_positions", this.permitted_positions);

        axios
          .post("api/training/upload/video", formData, {
            headers: {
              Authorization: "Bearer " + localStorage.getItem("access_token"),
              "Content-Type": "multipart/form-data",
            },
          })
          .then(
            (response) => {
              if (response.data.success) {
                // send data to Socket.IO Server
                // this.$socket.emit("sendData", { action: "import-project" });

                this.$swal({
                  position: "center",
                  icon: "success",
                  title: "Record has been uploaded",
                  showConfirmButton: false,
                  timer: 2500,
                });

                this.clear();
                this.dialog_upload = false;
                this.getVideos();
              } else {
                this.fileIsInvalid = true;
              }

              this.uploadDisabled = false;
              this.uploading = false;

              console.log(response.data);
            },
            (error) => {
              this.isUnauthorized(error);
              this.uploadDisabled = false;
              console.log(error);
            }
          );
      }
    },
    clear() {
      this.$v.$reset();
      this.dialog_upload = false;
      this.file = [];
      this.fileIsEmpty = false;
      this.title = "";
      this.description = "";
      this.permitted_positions = "";
    },
    playVideo(item) {
      let video_src = item.file_path + "/" + item.file_name;
      this.video_src = video_src;
      this.video_title = item.title;
      this.autoplay = true;
      this.dialog_video = true;
    },
    closeVideo() {
      this.autoplay = false;
      this.dialog_video = false;
      this.video_src = "";
      this.video_title = "";
    },
    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },
  },
  computed: {
    fileErrors() {
      const errors = [];
      if (!this.$v.file.$dirty) return errors;
      !this.$v.file.required && errors.push("File is required.");
      this.fileIsEmpty && errors.push("File is empty.");
      this.fileIsInvalid && errors.push("File type must be 'mp4'.");
      return errors;
    },
    titleErrors() {
      const errors = [];
      if (!this.$v.title.$dirty) return errors;
      !this.$v.title.required && errors.push("Title is required.");
      return errors;
    },
    permittedPositionsErrors() {
      const errors = [];
      if (!this.$v.permitted_positions.$dirty) return errors;
      !this.$v.permitted_positions.required &&
        errors.push("Permitted Positions is required.");
      return errors;
    },
    ...mapState("auth", ["user", "userIsLoaded"]),
    ...mapState("userRolesPermissions", [
      "userRoles",
      "userPermissions",
      "userRolesPermissionsIsLoaded",
    ]),
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getVideos();
  },
};
</script>