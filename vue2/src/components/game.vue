<template>
  <div>
    <div class="row alerts">
      <div v-if="alerts.success" class="row">
        <div class="col-sm-12">
          <div class="alert success">{{alerts.success}}</div>
        </div>
      </div>
      <div v-if="alerts.errors" class="row">
        <div class="col">
          <div v-for="(error, index) in alerts.errors" :key="index" class="alert errorr">{{error}}</div>
        </div>
      </div>
    </div>

  <div v-if="show_input">
    <div class="row content game">
      <div class="row body">
        <div class="col-sm-20">
              <div class="row rating">
                <div class="col-sm-5">
                  <label for="width">Width</label>
                </div>
                <div class="col-sm-15">
                  <input
                    id="width"
                    style="width: 100%;"
                    type="number"
                    v-model="width"
                  />
                </div>
              </div>

              <div class="row rating">
                <div class="col-sm-5">
                  <label for="height">Height</label>
                </div>
                <div class="col-sm-15">
                  <input
                    id="height"
                    type="number"
                    style="width: 100%;"
                    v-model="height"
                  />
                </div>
              </div>

              <div class="row rating">
                <div class="col-sm-5">
                  <label for="moves">Moves to win</label>
                </div>
                <div class="col-sm-15">
                  <input
                    id="moves"
                    type="number"
                    style="width: 100%;"
                    v-model="move_cnt_to_win"
                  />
                </div>
              </div>

              <div class="row rating">
                <div class="col-sm-20">
                  &nbsp;
                </div>
              </div>

              <div class="row rating">
                <div class="col-sm-20">
                  <button style="width: 100%;" type="button" @click="startGame()">Start</button>
                </div>
              </div>
              </div>
            </div>
          </div>
        </div>
        <div class="board" v-if=show_board>
          <div class="row" v-for="row in rows" :key="row.number">
            <div v-for="cell in row.columns" :key="cell.cell">
              <template v-if=cell.value>
                <div :class="computedClass" class="cell selected">
                  <span>{{cell.value}}</span>
                </div>
              </template>
              <template v-else>
                <div :class="computedClass" class="cell choose-me" @click="selectCell(cell)">
                  <span>{{cell.value}}</span>
                </div>
              </template>
              
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>
<script>
export default {
  data() {
    return {
      loading: true,
      alerts: { success: "", errors: [] },
      slug: this.$route.params.slug,
      type: this.$route.params.type,
      rows: [],
      show_board: false,
      show_input: false,
      width: 3,
      height:3,
      move_cnt_to_win: 3
    };
  },
  computed: {
    computedClass() {
      return  'col-sm-' + Math.floor(20 / this.width);      
    }
  },
  created() {
    this.getCurrentGame();
  },
  methods: {
    getCurrentGame() {
      this.$http
      .get("game").then(
        function onSuccess(response) {
          this.width = response.data.width;
          this.height = response.data.height;
          this.drawTheBoard(response.data.width, response.data.height)
        },
        function onFail(response) {
          this.loading = false;
          this.show_input = true;
        }
      );
    },
    drawTheBoard(width, height){
      this.width = width;
      this.height = height;

    // #16 Populate rows and columns based on the board dimensions.
      for(let i = 0; i < this.height; i++){
        let row = {number: i, columns: []};
        for(let j = 0; j < this.width; j++){
          let column = {row: i, column: j,value: null}
          row.columns.push(column);
        }
        this.rows.push(row);
      }

      this.loading = false;
      this.show_board = true;
      this.show_input = false;
    },
    selectCell(cell){
      if(cell.value === null){
        // #16 Send to API where it will be validated and stored.
        this.loading = true;
         this.$http.post("game/move", cell).then(
          function onSuccess(response) {
            this.loading = false;
            // #16 Sets X or O.
            cell.value = response.data.value;
          },
          function onFail(response) {
            this.loading = false;
            this.showError(response.data.errors);
          }
        );
      }
      return false;
    },
    startGame() {
      this.clearAlerts();

      this.show_input = false;
      this.loading = true;
      this.width = parseInt(this.width);
      this.height = parseInt(this.height);
      this.move_cnt_to_win = parseInt(this.move_cnt_to_win);
      this.$http
        .post("game", {
          width: this.width,
          height:this.height,
          move_cnt_to_win:this.move_cnt_to_win
        })
        .then(
          function onSuccess(response) {
            this.drawTheBoard(response.data.width, response.data.height)
          },
          function onFail(response) {
            this.loading = false;
            this.show_input = true;
            this.showError(response.data.errors);
          }
        );
    },
    clearAlerts: function() {
      (this.alerts.success = ""), (this.alerts.errors = []);
    },
    showSuccess: function(success = "Saved!") {
      this.alerts.success = this.getTranslatedMessage(success);
      this.loading = false;
    },
    showError: function(errors = ["Sorry, but there's a problem."]) {

      for(let key in errors){
        this.alerts.errors.push(key + ": " + errors[key]);
      }
      this.loading = false;
    },
    // TODO: Plug translations.
    getTranslatedMessage: function(messageKey) {
      return this.doesTranslationExist(messageKey)
        ? window.translations[messageKey]
        : messageKey;
    },
    doesTranslationExist(messageKey) {
      return (
        messageKey &&
        typeof window.translations != "undefined" &&
        typeof window.translations[messageKey] != "undefined" &&
        window.translations[messageKey] != null
      );
    }
  }
};
</script> 