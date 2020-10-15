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

    <div class="row content game">
      <div class="row body">
        <div class="col-sm-12">
          <div class="row title">
            <div class="col-sm-12">
              <h1>{{game.width}}</h1>
            </div>
          </div>
          <div class="row thumbnail">
            <div class="col-sm-12"></div>
          </div>
          <div v-if="show_input">
              <div class="row rating">
              <input
                id="width"
                style="width: 100%;"
                type="number"
                v-model="width"
                placeholder="Width. Ex. 3"
              />
              <input
                id="height"
                type="number"
                style="width: 100%;"
                v-model="height"
                placeholder="Price. Ex., 0.3"
              />
              <button type="button" @click="startGame()">Start</button>
              </div>
            </div>
          </div>
        </div>
        <div v-if=show_board>
          <div class="row body" v-for="index in height" :key="index">
            <div class="col-sm-4"  v-for="index2 in width" :key="index2">
              <div class="row thumbnail">
                <div class="col-sm-12">
                  aaaaaa
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
  data() {
    return {
      loading: true,
      alerts: { success: "", errors: [] },
      slug: this.$route.params.slug,
      type: this.$route.params.type,
      game: {},
      show_board: false,
      show_input: true,
      width: 3,
      height:3
    };
  },
  created() {
  },
  methods: {
    startGame() {
      this.clearAlerts();

      this.show_input = false;
      this.loading = true;
      this.width = parseInt(this.width);
      this.height = parseInt(this.height);

      this.$http
        .post("game/grid", {
          width: this.width,
          height:this.height
        })
        .then(
          function onSuccess(response) {
            this.loading = false;
            this.show_board = true;
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