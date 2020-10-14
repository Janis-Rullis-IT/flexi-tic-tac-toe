<template>
  <div>
		<div class="row header">
			<div class="row">
				<div class="col-sm-3 logo">
					<a href="/"><img alt="Home" src="/ui/img/logo-placeholder.jpg"></a>
				</div>
				<div class="col-sm-6"></div>
				<div class="col-sm-3">
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12 link-menu">&nbsp;</div>
			</div>
		</div>

		<slot></slot>
  </div>
</template>
<script>export default {name: 'default-layout'}</script>
