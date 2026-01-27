> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# Organizations & Projects

> Organize your teams, manage API keys, and control access with Organizations and Projects.

Black Forest Labs uses **Organizations** and **Projects** to help you manage team collaboration, API access, and billing. This structure provides enterprise-grade access control while remaining simple for individual developers.

## Core Concepts

<CardGroup cols={2}>
  <Card title="Organizations" icon="building">
    Your team or company. Manages team members, billing, credits, and contains multiple projects.
  </Card>

  <Card title="Projects" icon="folder">
    Workspaces within an organization. Each project has its own API keys and usage tracking.
  </Card>
</CardGroup>

<img src="https://mintcdn.com/bfl/oCk7e3Ubtt3U44gr/images/organization-structure.svg?fit=max&auto=format&n=oCk7e3Ubtt3U44gr&q=85&s=47016f81c3cfc23cd98c5e9a0f55d421" alt="Organization and project structure" data-og-width="800" width="800" data-og-height="520" height="520" data-path="images/organization-structure.svg" data-optimize="true" data-opv="3" srcset="https://mintcdn.com/bfl/oCk7e3Ubtt3U44gr/images/organization-structure.svg?w=280&fit=max&auto=format&n=oCk7e3Ubtt3U44gr&q=85&s=2330a716c687647bfb41d600a495b8b1 280w, https://mintcdn.com/bfl/oCk7e3Ubtt3U44gr/images/organization-structure.svg?w=560&fit=max&auto=format&n=oCk7e3Ubtt3U44gr&q=85&s=8fc42b296d15871b33a66d8bbb90d830 560w, https://mintcdn.com/bfl/oCk7e3Ubtt3U44gr/images/organization-structure.svg?w=840&fit=max&auto=format&n=oCk7e3Ubtt3U44gr&q=85&s=4d63d9c5c98ac0e2e328134a77e0b646 840w, https://mintcdn.com/bfl/oCk7e3Ubtt3U44gr/images/organization-structure.svg?w=1100&fit=max&auto=format&n=oCk7e3Ubtt3U44gr&q=85&s=1e13a39424fab4cab9d94c64056f8978 1100w, https://mintcdn.com/bfl/oCk7e3Ubtt3U44gr/images/organization-structure.svg?w=1650&fit=max&auto=format&n=oCk7e3Ubtt3U44gr&q=85&s=0853e5de516446a10a0a8d0a06dbd064 1650w, https://mintcdn.com/bfl/oCk7e3Ubtt3U44gr/images/organization-structure.svg?w=2500&fit=max&auto=format&n=oCk7e3Ubtt3U44gr&q=85&s=317610cea211f8a804a5db4c4cdd693f 2500w" />

<Info>
  When you create a BFL account, you automatically get a **Default Organization** and **Default Project** ready to use. You can rename these or create more at any time.
</Info>

## Managing Organizations & Projects

<Tabs>
  <Tab title="Organizations">
    ### Creating an Organization

    <Steps>
      <Step title="Open the selector">
        Click your organization name in the sidebar
      </Step>

      <Step title="Create new">
        Select **Create Organization** and enter a name
      </Step>

      <Step title="Add a project">
        Optionally create an initial project during setup
      </Step>
    </Steps>

    ### Organization Settings

    Access your organization settings to rename it, manage members, view the activity log, or delete it.

    <Warning>
      Deleting an organization is permanent. Transfer any remaining credits to another organization first.
    </Warning>
  </Tab>

  <Tab title="Projects">
    ### Creating a Project

    <Steps>
      <Step title="Navigate">
        Go to your organization in the sidebar
      </Step>

      <Step title="Create">
        Click **Create Project** and enter a name
      </Step>
    </Steps>

    ### Project Features

    <CardGroup cols={3}>
      <Card title="API Keys" icon="key">
        Create and manage keys scoped to this project
      </Card>

      <Card title="Usage Metrics" icon="chart-line">
        Track API consumption per project
      </Card>

      <Card title="Members" icon="user-plus">
        Add project-specific collaborators
      </Card>
    </CardGroup>
  </Tab>
</Tabs>

## API Keys

<Info>
  API keys are **scoped to projects**, providing better security and organization than account-level keys.
</Info>

### Creating API Keys

<Steps>
  <Step title="Navigate to your project">
    Select your project from the sidebar
  </Step>

  <Step title="Go to API → Keys">
    Click **Add Key** and give it a descriptive name
  </Step>

  <Step title="Copy immediately">
    The full key is only shown once — store it securely
  </Step>
</Steps>

All BFL API keys use the format `bfl_<random-string>`. In your dashboard, you'll see a preview like `bfl_ABC...xyz` to help identify keys.

## Environment Separation

We recommend using **separate projects** for different environments:

<Steps>
  <Step title="Create environment projects">
    Set up `Production`, `Staging`, and `Development` projects
  </Step>

  <Step title="Generate environment-specific keys">
    Name keys clearly: `prod-server-1`, `staging-main`, `dev-local`
  </Step>
</Steps>

<Tip>
  This approach gives you independent usage tracking per environment and a clear audit trail for each project.
</Tip>

## Best Practices

<AccordionGroup>
  <Accordion title="Solo Developers" icon="user">
    Create separate projects for production vs development to keep usage tracking clean.
  </Accordion>

  <Accordion title="Teams & Startups" icon="users">
    One organization for your company, with projects per app or environment. Use roles to control who can manage billing vs API keys.
  </Accordion>

  <Accordion title="Agencies" icon="briefcase">
    Create one organization per client, or use project-only access for client stakeholders within your agency org.
  </Accordion>

  <Accordion title="Enterprise" icon="building">
    Organizations per department, projects per application. Use activity logs for compliance.
  </Accordion>
</AccordionGroup>

## For Existing Users

<Note>
  If you had a BFL account before December 2025, you've been automatically migrated. A "Default" organization and project were created, your credits moved to the organization level, and **your existing API keys continue to work unchanged**.
</Note>

## Next Steps

<CardGroup cols={2}>
  <Card title="Team Management" icon="users" href="/account_management/team_management">
    Learn about roles and inviting team members
  </Card>

  <Card title="Credits & Billing" icon="credit-card" href="/account_management/credits_billing">
    Manage your credits and billing
  </Card>
</CardGroup>
