/** @type {import('@docusaurus/plugin-content-docs').SidebarsConfig} */
const sidebars = {
  tutorialSidebar: [
    'intro',
    {
      type: 'category',
      label: 'Operasional Inti',
      items: [
        'user-management',
        'attendance-scheduling',
        'finances',
      ],
    },
    {
      type: 'category',
      label: 'Keamanan & Integritas',
      items: [
        'database-schema',
        'maintenance-health',
      ],
    },
    {
      type: 'category',
      label: 'Panduan Developer & DevOps',
      items: [
        'api-integration',
        'deployment-hosting',
        'troubleshooting-faq',
      ],
    },
  ],
};

export default sidebars;
