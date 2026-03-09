let sidebar;
try {
  sidebar = require("./api-docs/sidebar.js");
} catch {
  sidebar = [];
}

module.exports = {apiSidebar: sidebar};
