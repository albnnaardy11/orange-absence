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
      label: 'Security & Integrity',
      items: [
        'database-schema',
        'maintenance-health',
      ],
    },
    {
      type: 'category',
      label: 'Developer & DevOps',
      items: [
        'api-integration',
        'deployment-hosting',
        'troubleshooting-faq',
      ],
    },
  ],
};

export default sidebars;
