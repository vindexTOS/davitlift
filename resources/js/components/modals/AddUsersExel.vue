     <v-dialog v-model="dialogVisible" :width="dialogWidth">
      <v-card>
        <v-card-title>{{ title }}</v-card-title>
        <v-card-text>
          <v-text-field
            v-model="editedAmount"
            :label="label"
            type="number"
            required
          ></v-text-field>
          <div class="drop-zone" @drop.prevent="handleDrop" @dragover.prevent>
            <p v-if="!excelFile">{{ dropZoneText }}</p>
            <p v-else>Uploaded File: {{ excelFile.name }}</p>
            <v-btn @click="uploadFile" v-if="excelFile">Upload</v-btn>
          </div>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="closeDialog">{{ closeText }}</v-btn>
          <v-btn @click="save">{{ saveText }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
 
  
  <script>
  import * as XLSX from "xlsx";
  
  export default {
    name: "EditableDialog",
    props: {
      dialogVisible: {
        type: Boolean,
        required: true,
      },
      title: {
        type: String,
        default: "Edit Amount",
      },
      label: {
        type: String,
        default: "Enter Amount",
      },
      closeText: {
        type: String,
        default: "Close",
      },
      saveText: {
        type: String,
        default: "Save",
      },
      dropZoneText: {
        type: String,
        default: "Drag and drop an Excel file here or click to upload",
      },
      dialogWidth: {
        type: String,
        default: "500px",
      },
    },
    data() {
      return {
        editedAmount: null,
        excelFile: null,
      };
    },
    methods: {
      closeDialog() {
        this.$emit("update:dialogVisible", false);
      },
      save() {
        this.$emit("save", this.editedAmount);
      },
      handleDrop(event) {
        const file = event.dataTransfer.files[0];
        if (file && file.type.includes("spreadsheet")) {
          this.excelFile = file;
          this.processExcelFile(file);
        } else {
          alert("Please upload a valid Excel file.");
        }
      },
      uploadFile() {
        if (this.excelFile) {
          this.processExcelFile(this.excelFile);
        } else {
          alert("No file selected.");
        }
      },
      processExcelFile(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          const data = e.target.result;
          const workbook = XLSX.read(data, { type: "binary" });
          const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
          const excelData = XLSX.utils.sheet_to_json(firstSheet);
          console.log("Excel Data as Array:", excelData);
        };
        reader.readAsBinaryString(file);
      },
    },
  };
  </script>
  
  <style>
  .drop-zone {
    border: 2px dashed #ccc;
    padding: 20px;
    text-align: center;
    cursor: pointer;
  }
  .drop-zone:hover {
    border-color: #aaa;
  }
  </style>
  