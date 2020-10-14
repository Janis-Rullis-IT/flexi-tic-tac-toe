<template>
	<default-layout>
		<game></game>
	</default-layout>
</template>
<script>
	import defaultLayout from '../layouts/Default.vue';
	import game from '../components/game.vue';
	export default {name: 'GamePage', components: {defaultLayout, game}}
</script>
