/** @type {import('@docusaurus/plugin-content-docs').SidebarsConfig} */
const sidebars = {
  tutorialSidebar: [
    'intro',
    {
      type: 'category',
      label: 'Core Operations',
      items: [
        'user-management',
        'attendance-scheduling',
        'finances',
      ],
    },
    {
      type: 'category',
      label: 'Developer Guide',
      items: [
        'api-integration',
      ],
    },
  ],
};

export default sidebars;
