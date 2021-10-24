import React from 'react';
import { Link } from 'react-router-dom';

import { HeartIcon, EditIcon } from '../../icons';

import PageTitle from '../../components/Typography/PageTitle';
import SectionTitle from '../../components/Typography/SectionTitle';

import { Button } from '@windmill/react-ui';

function Buttons() {
  return (
    <>
      <PageTitle>Buttons</PageTitle>

      <SectionTitle>Primary</SectionTitle>
      <div className="flex flex-col flex-wrap mb-8 space-y-4 md:flex-row md:items-end md:space-x-4">
        <div>
          <Button size="larger">Larger Button</Button>
        </div>

        <div>
          <Button size="large">Large Button</Button>
        </div>

        <div>
          <Button>Regular</Button>
        </div>

        <div>
          <Button tag={Link.toString()}>
            Router Link
          </Button>
        </div>

        <div>
          <Button disabled>Disabled</Button>
        </div>

        <div>
          <Button size="small">Small</Button>
        </div>
      </div>

      <SectionTitle>Outline</SectionTitle>
      <div className="flex flex-col flex-wrap mb-8 space-y-4 md:flex-row md:items-end md:space-x-4">
        <div>
          <Button layout="outline" size="larger">
            Larger Button
          </Button>
        </div>

        <div>
          <Button layout="outline" size="large">
            Large Button
          </Button>
        </div>

        <div>
          <Button layout="outline">Regular</Button>
        </div>

        <div>
          <Button layout="outline" tag={Link.toString()}>
            Router Link
          </Button>
        </div>

        <div>
          <Button layout="outline" disabled>
            Disabled
          </Button>
        </div>

        <div>
          <Button layout="outline" size="small">
            Small
          </Button>
        </div>
      </div>

      <SectionTitle>Link</SectionTitle>
      <div className="flex flex-col flex-wrap mb-8 space-y-4 md:flex-row md:items-end md:space-x-4">
        <div>
          <Button layout="link" size="larger">
            Larger Button
          </Button>
        </div>

        <div>
          <Button layout="link" size="large">
            Large Button
          </Button>
        </div>

        <div>
          <Button layout="link">Regular</Button>
        </div>

        <div>
          <Button layout="link" tag={Link.toString()}>
            Router Link
          </Button>
        </div>

        <div>
          <Button layout="link" disabled>
            Disabled
          </Button>
        </div>

        <div>
          <Button layout="link" size="small">
            Small
          </Button>
        </div>
      </div>

      <SectionTitle>Icons</SectionTitle>
      <div className="flex flex-col flex-wrap mb-8 space-y-4 md:flex-row md:items-end md:space-x-4">
        <div>
          {/* @ts-ignore */}
          <Button iconRight={HeartIcon}>
            <span>Icon right</span>
          </Button>
        </div>

        <div>
          {/* @ts-ignore */}
          <Button iconLeft={HeartIcon}>
            <span>Icon Left</span>
          </Button>
        </div>

        <div>
          {/* @ts-ignore */}
          <Button icon={HeartIcon} aria-label="Like" />
        </div>

        <div>
          {/* @ts-ignore */}
          <Button icon={EditIcon} aria-label="Edit" />
        </div>

        <div>
          {/* @ts-ignore */}
          <Button icon={HeartIcon} layout="link" aria-label="Like" />
        </div>
        <div>
          {/* @ts-ignore */}
          <Button icon={HeartIcon} layout="outline" aria-label="Like" />
        </div>
      </div>
    </>
  );
};

export default Buttons;
