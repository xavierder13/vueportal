<template>
  <div>
    <v-card>
      <v-card-title>
        Branches 
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search"
          single-line
        ></v-text-field>
        <v-spacer></v-spacer>
      </v-card-title>
      <v-data-table
        :headers="headers"
        :items="branches"
        :search="search"
        :loading="loading"
        group-by="name"
        class="elevation-1"
        :expanded.sync="expanded"
        loading-text="Loading... Please wait"
        v-if="canViewList"
      >
        <template v-slot:item.date_uploaded="{ item }">
          <v-chip color="secondary" v-if="item.date_uploaded">
            {{ item.date_uploaded }}
          </v-chip>
        </template>
        <template v-slot:group.header="{ items, headers, toggle, isOpen, }">
          <td colspan="3">
            <v-row>
              <v-col>
                <v-btn @click="toggle" small icon :ref="items" :data-open="isOpen">
                  <v-icon v-if="isOpen">mdi-chevron-up</v-icon>
                  <v-icon v-else>mdi-chevron-down</v-icon>
                </v-btn>
                {{ items[0].name }}
              </v-col>
            </v-row>
          </td>
          <td> 
            <v-menu offset-y v-if="canDownloadTemplate && canImport">
              <template v-slot:activator="{ on, attrs }">
                <v-btn x-small v-bind="attrs" v-on="on" class="primary--text">
                  Actions
                  <v-icon small> mdi-menu-down </v-icon>
                </v-btn>
              </template>
              <v-list class="pa-1">
                <v-list-item class="ma-0 pa-0" style="min-height: 25px">
                  <v-list-item-title>
                    <v-btn x-small @click="importExcel(items)" class="mx-1" width="100px" color="primary">
                      <v-icon class="mr-1" x-small>
                        mdi-import
                      </v-icon>
                      Import
                    </v-btn>
                  </v-list-item-title>
                </v-list-item>
                <v-list-item class="ma-0 pa-0" style="min-height: 25px" v-if="canExport">
                  <v-list-item-title>
                    <v-btn class="mx-1 white--text" x-small @click="downloadTemplate(items)" width="100px" color="#AB47BC">
                      <v-icon class="mr-1" x-small> mdi-download </v-icon>
                      Template
                    </v-btn>
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
            <v-btn x-small color="primary" @click="importExcel(items)" v-if="canImport && !canDownloadTemplate"> 
              <v-icon x-small class="mr-1">mdi-upload</v-icon> import
            </v-btn> 
          </td>
        </template>
        <template v-slot:item="{ item }">
          <tr v-for="(value, index) in item.file_upload_logs">
            <td> </td>
            <td>
              <v-chip color="secondary">
                {{ value.date_uploaded }}
              </v-chip>
            </td>
            <td> 
              <v-chip color="secondary">
                {{ value.docdate }}
              </v-chip> </td>
            <td>
              <v-menu offset-y v-if="canImport || canExport || canDownload">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn x-small v-bind="attrs" v-on="on" class="primary--text">
                    Actions
                    <v-icon small> mdi-menu-down </v-icon>
                  </v-btn>
                </template>
                <v-list class="pa-1">
                  <v-list-item class="ma-0 pa-0" style="min-height: 25px">
                    <v-list-item-title>
                      <v-btn x-small @click="viewList(value)" class="mx-1" width="100px" color="info">
                        <v-icon class="mr-1" x-small>
                          mdi-eye
                        </v-icon>
                        View
                      </v-btn>
                    </v-list-item-title>
                  </v-list-item>
                  <v-list-item class="ma-0 pa-0" style="min-height: 25px" v-if="canExport">
                    <v-list-item-title>
                      <v-btn class="mx-1" x-small @click="exportData(value)" width="100px" color="success">
                        <v-icon class="mr-1" x-small> mdi-microsoft-excel </v-icon>
                        Export
                      </v-btn>
                    </v-list-item-title>
                  </v-list-item>
                  <v-list-item class="ma-0 pa-0" style="min-height: 25px" v-if="canDownload">
                    <v-list-item-title>
                      <v-btn class="mx-1" x-small @click="downloadFile(value)" width="100px" color="primary" >
                        <v-icon class="mr-1" small> mdi-download </v-icon>
                        Download
                      </v-btn>
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </td>
          </tr>
        </template>
      </v-data-table>
    </v-card>
  </div>
</template>

<script>

export default {
  name: "DataTableGroup",
  props: [
    'branches',
    'loading',
    'canViewList',
    'canImport',
    'canExport',
    'canDownload',
    'canDownloadTemplate',
  ],
  data() {
    return {
      search: "",
      headers: [
        { text: "Branch", value: "branch" },
        { text: "Date Uploaded", value: "date_uploaded" },
        { text: "Document Date", value: "docdate" },
        { text: "Actions", value: "actions", sortable: false, width: "120px" },
      ],
      expanded: [],
    }
  },
  methods: {
    importExcel(item) {
      this.$emit('importExcel', item);
    },
    exportData(item) {
      this.$emit('exportData', item);
    },
    downloadFile(item) {
      this.$emit('downloadFile', item);
    },
    downloadTemplate(item) {
      this.$emit('downloadTemplate', item);
    },
    viewList(item) {
      this.$emit('viewList', item);
    }
  }
}

</script>
