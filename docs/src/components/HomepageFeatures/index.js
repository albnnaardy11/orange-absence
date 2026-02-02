import clsx from 'clsx';
import Heading from '@theme/Heading';
import styles from './styles.module.css';

const FeatureList = [
  {
    title: 'Automated Attendance',
    description: (
      <>
        System tracking leveraging QR codes and division-based schedules. 
        Zero manual overhead for daily check-ins.
      </>
    ),
  },
  {
    title: 'Financial Tracking',
    description: (
      <>
        Automated weekly billing for division cash (Kas). 
        Monitor arrears and payments through a unified dashboard.
      </>
    ),
  },
  {
    title: 'API Reference',
    description: (
      <>
        Full OpenAPI/Swagger support out of the box. 
        Detailed specs for authentication and core entities.
      </>
    ),
  },
];

function Feature({title, description}) {
  return (
    <div className={clsx('col col--4')}>
      <div className="text--center padding-horiz--md">
        <Heading as="h3">{title}</Heading>
        <p>{description}</p>
      </div>
    </div>
  );
}

export default function HomepageFeatures() {
  return (
    <section className={styles.features}>
      <div className="container">
        <div className="row">
          {FeatureList.map((props, idx) => (
            <Feature key={idx} {...props} />
          ))}
        </div>
      </div>
    </section>
  );
}
