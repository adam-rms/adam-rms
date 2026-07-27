import type {SidebarsConfig} from "@docusaurus/plugin-content-docs";

const sidebars: SidebarsConfig = {};

try {
  // eslint-disable-next-line @typescript-eslint/no-require-imports
  const generated = require("./api-docs/sidebar.ts");
  sidebars.apisidebar = generated.default ?? generated;
} catch {
  // API docs not yet generated — sidebar will be empty
}

export default sidebars;
