<template>
	<div>
		<cdx-radio
			v-model="radioValue"
			:name="name"
			:input-value="radioInputValue"
			@change="updateRadio">
			{{ label }}
		</cdx-radio>
		<cdx-text-input
			v-if="shouldShowFreeTextInput"
			v-model="freeTextValue"
			type="text"
			class="quick-survey-radio-free-text"
			:placeholder="textInputPlaceholder"
			@input="updateText"></cdx-text-input>
	</div>
</template>

<script>
const codex = require( '@wikimedia/codex' );

// @vue/component
module.exports = exports = {
	name: 'AnswerRadioOption',
	components: {
		CdxRadio: codex.CdxRadio,
		CdxTextInput: codex.CdxTextInput
	},
	props: {
		name: {
			type: String,
			default: 'answer-radio-option'
		},
		label: {
			type: String,
			default: ''
		},
		textInputPlaceholder: {
			type: String,
			required: false,
			default: null
		},
		radioInputValue: {
			type: String,
			default: ''
		},
		modelValue: {
			type: Object,
			default: function () {
				return { radio: null, text: null };
			}
		}
	},
	emits: [ 'update:freeTextValue', 'update:modelValue' ],
	data: function () {
		return {
			radioValue: this.modelValue.radio,
			freeTextValue: this.modelValue.text
		};
	},
	computed: {
		/**
		 * @return {boolean}
		 */
		shouldShowFreeTextInput: function () {
			return this.textInputPlaceholder &&
				this.modelValue.radio === this.radioInputValue;
		}
	},
	methods: {
		updateRadio: function ( event ) {
			if ( event.target.checked ) {
				this.$emit( 'update:modelValue', {
					radio: event.target.value,
					text: this.freeTextValue
				} );
			}
		},
		updateText: function ( event ) {
			if ( this.modelValue.radio &&
				this.modelValue.radio === this.radioInputValue ) {
				this.$emit( 'update:modelValue', {
					radio: this.radioValue,
					text: event.target.value
				} );
			}
		}
	}
};
</script>

<style lang="less">
@import 'mediawiki.skin.variables.less';

.quick-survey-radio-free-text {
	margin-inline-start: @spacing-150;

	.cdx-text-input__input {
		width: calc( 100% - @spacing-100 );
	}
}
</style>
