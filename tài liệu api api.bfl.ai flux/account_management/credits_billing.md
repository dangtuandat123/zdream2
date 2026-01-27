> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# Credits & Billing

> Add credits, track usage, and handle billing for your organization

## Understanding Credits

<Info>
  Credits are managed at the **organization level** and shared across all projects. All team members draw from the same credit pool, while usage is tracked per project so you can see which projects consume the most.
</Info>

## Adding Credits

<Steps>
  <Step title="Log in to your dashboard">
    Go to [dashboard.bfl.ai](https://dashboard.bfl.ai) and sign in
  </Step>

  <Step title="Navigate to Credits">
    In your organization sidebar, go to **API → Credits**
  </Step>

  <Step title="Select credit amount">
    Click **Add Credits** and choose the amount you want to purchase
  </Step>

  <Step title="Complete payment">
    You'll be redirected to Stripe to complete your payment securely. Credits are available immediately.
  </Step>
</Steps>

<Tip>
  Check our [Pricing](/quick_start/pricing) page for the latest pricing for each model.
</Tip>

<Frame caption="Click Add Credits to get started">
  <img src="https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/add_credits.jpg?fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=8c98e18b3f4278ad9a047a4eb4a6dd04" alt="Add credits button in dashboard" data-og-width="533" width="533" data-og-height="262" height="262" data-path="images/add_credits.jpg" data-optimize="true" data-opv="3" srcset="https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/add_credits.jpg?w=280&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=594327b6bf7dd5bbc0151eb3c064ee42 280w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/add_credits.jpg?w=560&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=7a7af2a1cd89dd4b364cba43b5651d37 560w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/add_credits.jpg?w=840&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=5be502297daf84b6903549ae2d509dee 840w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/add_credits.jpg?w=1100&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=427e6ca8c773ff34b4cb34124ab96fa1 1100w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/add_credits.jpg?w=1650&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=d190b3907478b07b6519a719fe14799d 1650w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/add_credits.jpg?w=2500&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=831b33d63f85945f3d71745c07dd47d9 2500w" />
</Frame>

## Payment & Billing

<CardGroup cols={2}>
  <Card title="Payment Methods" icon="credit-card">
    We accept all major credit cards through Stripe.
  </Card>

  <Card title="Invoices" icon="file-invoice">
    Go to **API → Credits**, find your purchase in **Purchase History**, and click **View Invoice**.
  </Card>
</CardGroup>

<Frame caption="Stripe payment interface">
  <img src="https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/stripe.jpg?fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=3dfac3f836315046852dc69bb489ee3b" alt="Stripe payment interface" data-og-width="948" width="948" data-og-height="913" height="913" data-path="images/stripe.jpg" data-optimize="true" data-opv="3" srcset="https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/stripe.jpg?w=280&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=c7601d4ece94b3575b645efb5f0c555f 280w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/stripe.jpg?w=560&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=9f53d27720eef77e755b180aa873acc9 560w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/stripe.jpg?w=840&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=52eeeaafe92053b35970527814f39281 840w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/stripe.jpg?w=1100&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=380cf655a455f9dfdfde108f750f03df 1100w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/stripe.jpg?w=1650&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=91dd5829933443b6f18cc248d6b8703b 1650w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/stripe.jpg?w=2500&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=e1cb6dc45bf98070f953b7a0ce326162 2500w" />
</Frame>

## Track Your Usage

Monitor your API consumption at the organization or project level:

<Tabs>
  <Tab title="Organization Usage">
    See total consumption across all projects at **API → Usage** in your organization view:

    <CardGroup cols={3}>
      <Card title="Credit Balance" icon="coins">
        Your current available credits
      </Card>

      <Card title="Aggregated Usage" icon="chart-pie">
        Total consumption across all projects
      </Card>

      <Card title="Model Breakdown" icon="layer-group">
        Cost breakdown per model
      </Card>
    </CardGroup>
  </Tab>

  <Tab title="Project Usage">
    Drill down into individual project usage at **API → Usage** within each project:

    <CardGroup cols={2}>
      <Card title="Project Consumption" icon="chart-line">
        Credits used by this specific project
      </Card>

      <Card title="Requests by Endpoint" icon="code">
        See which endpoints are used most
      </Card>

      <Card title="Usage Patterns" icon="clock">
        Daily and hourly usage trends
      </Card>

      <Card title="Model Costs" icon="receipt">
        Cost breakdown per model
      </Card>
    </CardGroup>
  </Tab>
</Tabs>

<Frame caption="Monitor your API usage">
  <img src="https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/dashboard_usage.png?fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=29bd07d8786c3a6352072bf9691e994a" alt="Usage dashboard" data-og-width="772" width="772" data-og-height="1239" height="1239" data-path="images/dashboard_usage.png" data-optimize="true" data-opv="3" srcset="https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/dashboard_usage.png?w=280&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=c0f78fe992c0176ba53a43fb9cebc69e 280w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/dashboard_usage.png?w=560&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=755e419231ac21305c3115651204ee41 560w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/dashboard_usage.png?w=840&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=08035e13cfb8d0543a174b7ab3a4a6f6 840w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/dashboard_usage.png?w=1100&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=464233beea0accdc0f7f0e986fa784bd 1100w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/dashboard_usage.png?w=1650&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=f2879ec96b1d2ab85e7215b07a3406c3 1650w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/dashboard_usage.png?w=2500&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=6ebfefc39719e4a660b9545b6498d1fc 2500w" />
</Frame>

## Transferring Credits

<Note>
  Organization administrators can transfer credits between organizations they manage. This is useful when consolidating credits, moving credits before deleting an organization, or reallocating budget between teams.
</Note>

To transfer credits, go to your organization settings or contact support.

## Activity Log

<Info>
  Track all billing and credit-related activities in your organization's **Activity Log**: credit purchases, credit transfers, and member changes that affect billing access. This provides a complete audit trail for compliance and accounting.
</Info>

## Next Steps

<CardGroup cols={2}>
  <Card title="Organizations & Projects" icon="building" href="/account_management/organizations_projects">
    Learn more about structuring your workspace
  </Card>

  <Card title="Team Management" icon="users" href="/account_management/team_management">
    Invite members and manage roles
  </Card>
</CardGroup>
