> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# Team Management

> Invite team members, assign roles, and manage access to your organizations and projects.

Collaborate with your team by inviting members to your organizations and projects. Our role-based access control lets you give each team member exactly the permissions they need.

## Roles Overview

<Tabs>
  <Tab title="Organization Roles">
    When you invite someone to an **organization**, they get access based on their role:

    <AccordionGroup>
      <Accordion title="Administrator" icon="shield">
        **Full control** over the organization — manage members, billing, settings, and all projects.
      </Accordion>

      <Accordion title="Developer" icon="code">
        **Build and ship** — create projects, manage API keys, and view usage.
      </Accordion>

      <Accordion title="Billing Manager" icon="credit-card">
        **Handle finances** — manage credits and subscriptions, view usage (no API access).
      </Accordion>

      <Accordion title="Viewer" icon="eye">
        **Read-only access** — view everything, change nothing.
      </Accordion>
    </AccordionGroup>

    <Note>
      Organization roles are automatically inherited by every project within the organization.
    </Note>
  </Tab>

  <Tab title="Project Roles">
    Invite members directly to a **specific project** without organization-wide access:

    <AccordionGroup>
      <Accordion title="Administrator" icon="shield">
        **Full project control** — manage project settings, API keys, and members.
      </Accordion>

      <Accordion title="Developer" icon="code">
        **Build within project** — manage API keys and view usage.
      </Accordion>

      <Accordion title="Billing Manager" icon="credit-card">
        **Project finances** — view usage metrics (no API access).
      </Accordion>

      <Accordion title="Viewer" icon="eye">
        **Read-only** — view project info, keys, and usage.
      </Accordion>
    </AccordionGroup>

    <Note>
      **Project-only members** can only see the specific projects they're invited to. They cannot see other projects or organization-level settings.
    </Note>
  </Tab>
</Tabs>

## Inviting Team Members

<Tabs>
  <Tab title="Invite to Organization">
    Inviting someone to your organization gives them access based on their role:

    <Steps>
      <Step title="Navigate to Members">
        In your organization sidebar, click **Members**
      </Step>

      <Step title="Click Invite Member">
        Click the **Invite Member** button
      </Step>

      <Step title="Enter details">
        Enter their email address and select their role
      </Step>

      <Step title="Send invitation">
        Click **Send Invitation** — they'll receive an email with a link to join
      </Step>
    </Steps>
  </Tab>

  <Tab title="Invite to Project Only">
    For contractors, consultants, or team members who only need access to specific projects:

    <Steps>
      <Step title="Navigate to project">
        Select the project from your sidebar
      </Step>

      <Step title="Go to Members">
        Click **Members** in the project menu
      </Step>

      <Step title="Invite member">
        Click **Invite Member**, enter their email, and select a project role
      </Step>
    </Steps>

    <Info>
      Project-only members can only see the projects they're explicitly invited to — they cannot view organization settings, other projects, or organization-wide billing.
    </Info>
  </Tab>
</Tabs>

<Tip>
  Invitations expire after **7 days**. You can cancel pending invitations and resend if needed.
</Tip>

## Managing Members

<CardGroup cols={3}>
  <Card title="Change Role" icon="arrows-rotate">
    Go to **Members**, click a member's current role, and select a new one. Changes take effect immediately.
  </Card>

  <Card title="Remove Member" icon="user-minus">
    Go to **Members**, find the member, and click remove. They immediately lose access.
  </Card>

  <Card title="Cancel Invitation" icon="xmark">
    Go to **Members**, find the pending invitation, and click **Cancel** to revoke it.
  </Card>
</CardGroup>

<Warning>
  When you remove someone, their API keys remain active. Rotate keys if the removed member had access to them.
</Warning>

## Best Practices

<AccordionGroup>
  <Accordion title="Startups & Small Teams" icon="rocket">
    * Make 2-3 people Administrators for redundancy
    * Use Developer role for engineers
    * Consider Billing Manager for finance/ops
  </Accordion>

  <Accordion title="Agencies" icon="briefcase">
    * Create separate organizations per client, OR
    * Use project-only access for client stakeholders
    * Keep API key access limited to your team
  </Accordion>

  <Accordion title="Enterprise" icon="building">
    * Use Administrators sparingly (principle of least privilege)
    * Billing Manager for finance team
    * Viewer role for stakeholders who need visibility
    * Project-only access for contractors
  </Accordion>
</AccordionGroup>

## Activity Tracking

<Info>
  All member-related actions are logged in your organization's **Activity Log**: invitations sent, invitations accepted, role changes, and member removals. This provides a complete audit trail for compliance and security reviews.
</Info>

## Next Steps

<CardGroup cols={2}>
  <Card title="Organizations & Projects" icon="building" href="/account_management/organizations_projects">
    Learn more about structuring your workspace
  </Card>

  <Card title="Credits & Billing" icon="credit-card" href="/account_management/credits_billing">
    Manage your credits and billing
  </Card>
</CardGroup>
