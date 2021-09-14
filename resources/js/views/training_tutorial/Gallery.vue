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
              <v-col cols="3">
                <v-card>
                  <v-img src="/img/film-colored.png" class="ml-4 mr-4"></v-img>

                  <v-card-title class="justify-center">
                    MSS Training Tutorials
                  </v-card-title>
                  <v-card-actions class="justify-center">
                    <v-btn
                      rounded
                      class="primary mb-2 pa-4"
                      @click="playVideo()"
                    >
                      <v-icon class="mr-1">mdi-play-circle</v-icon> Play
                      Video</v-btn
                    >
                  </v-card-actions>
                </v-card>
              </v-col>
              <v-col cols="3">
                <v-card>
                  <v-img src="/img/film-colored.png" class="ml-4 mr-4"></v-img>

                  <v-card-title class="justify-center">
                    MSS Training Tutorials
                  </v-card-title>
                  <v-card-actions class="justify-center">
                    <v-btn rounded class="primary mb-2 pa-4">
                      <v-icon class="mr-1">mdi-play-circle</v-icon> Play
                      Video</v-btn
                    >
                  </v-card-actions>
                </v-card>
              </v-col>
              <v-col cols="3">
                <v-card>
                  <v-img src="/img/film-colored.png" class="ml-4 mr-4"></v-img>

                  <v-card-title class="justify-center">
                    MSS Training Tutorials
                  </v-card-title>
                  <v-card-actions class="justify-center">
                    <v-btn rounded class="primary mb-2 pa-4">
                      <v-icon class="mr-1">mdi-play-circle</v-icon> Play
                      Video</v-btn
                    >
                  </v-card-actions>
                </v-card>
              </v-col>
              <v-col cols="3">
                <v-card>
                  <v-img src="/img/film-colored.png" class="ml-4 mr-4"></v-img>
                  <v-card-title class="justify-center">
                    {{ 'MSS SAP BUSINESS ONE Training Tutorials'.length > 20 ? 'MSS SAP BUSINESS ONE Training Tutorials'.substr(0, 17) + '...' : 'MSS SAP BUSINESS ONE Training Tutorials'}}
                  </v-card-title>
                  <v-card-actions class="justify-center">
                    <v-btn rounded class="primary mb-2 pa-4">
                      <v-icon class="mr-1">mdi-play-circle</v-icon> Play
                      Video</v-btn
                    >
                  </v-card-actions>
                </v-card>
              </v-col>
              <v-col cols="3">
                <v-card>
                  <v-img src="/img/film-colored.png" class="ml-4 mr-4"></v-img>

                  <v-card-title class="justify-center">
                    MSS Training Tutorials
                  </v-card-title>
                  <v-card-actions class="justify-center">
                    <v-btn rounded class="primary mb-2 pa-4">
                      <v-icon class="mr-1">mdi-play-circle</v-icon> Play
                      Video</v-btn
                    >
                  </v-card-actions>
                </v-card>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
        <v-dialog v-model="dialog_import" max-width="500px" persistent>
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
                @click="dialog_import = false"
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
        <v-dialog v-model="dialog_video" max-width="1100px" persistent>
          <v-card>
            <v-card-title>
              <span class="headline">Sample Video</span>
              <v-spacer></v-spacer>
              <v-icon @click="closeVideo()"> mdi-close </v-icon>
            </v-card-title>
            <v-card-text>
              <v-row>
                <v-col class="text-center">
                  <Media
                    :kind="'video'"
                    :isMuted="false"
                    :src="[
                      '/wysiwyg/2021-09-14/1631583904MOVIES 2020 Full MOVIE Action Movie 2021 Full Movie English Action Movies 2021 45.mp4',
                    ]"
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
      dialog_import: false,
      dialog_video: false,
      search: "",
      autoplay: false,
    };
  },
  methods: {
    uploadFile() {
      this.dialog_import = true;
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
                this.$v.$reset();
                this.dialog_import = false;
                this.file = [];
                this.fileIsEmpty = false;
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
    playVideo() {
      this.autoplay = true;
      this.dialog_video = true;
    },
    closeVideo() {
      this.autoplay = false;
      this.dialog_video = false;
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
    ...mapState("auth", ["user", "userIsLoaded"]),
    ...mapState("userRolesPermissions", [
      "userRoles",
      "userPermissions",
      "userRolesPermissionsIsLoaded",
    ]),
  },
  mounted() {},
};
</script>