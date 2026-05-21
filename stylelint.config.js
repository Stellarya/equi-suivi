export default {
  extends: ["stylelint-config-standard-scss"],
  rules: {
    // Désactivation complète des patterns de sélecteurs et formats de fonctions
    "selector-class-pattern": null,
    "color-function-notation": null,
    "alpha-value-notation": null,
    
    // On ignore les alertes sur les préfixes vendeurs et mots-clés
    "property-no-vendor-prefix": null,
    "value-keyword-case": null,
    
    // On ignore la mise en page cosmétique
    "rule-empty-line-before": null,
    "at-rule-empty-line-before": null,
    "block-no-empty": null,
    "scss/double-slash-comment-empty-line-before": null,
    "scss/double-slash-comment-whitespace-inside": null,
    "scss/dollar-variable-empty-line-before": null
  }
};