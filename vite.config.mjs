import { createViteConfig } from "vite-config-factory";

const entries = {
        'js/algolia-index-typesense-provider': './source/js/algolia-index-typesense-provider.ts',
        'css/algolia-index-typesense-provider': './source/sass/algolia-index-typesense-provider.scss',
};

export default createViteConfig(entries, {
	outDir: "assets/dist",
	manifestFile: "manifest.json",
});
