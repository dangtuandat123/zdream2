> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# Interactive Prompt Builder

> Build and experiment with FLUX prompts using our interactive layer-by-layer tool

export const PromptBuilder = () => {
  const [foundationIndex, setFoundationIndex] = useState(0);
  const [visualLayer, setVisualLayer] = useState("");
  const [technicalLayer, setTechnicalLayer] = useState("");
  const [atmosphericLayer, setAtmosphericLayer] = useState("");
  const [copied, setCopied] = useState(false);
  const foundationOptions = ["Retro game style detective in old school suit, upper body shot", "Close-up of a vintage car hood under heavy rain", "A cute round rusted robot repairing a classic pickup truck", "Abstract expressionist painting of a cute cat face", "A lone warrior in samurai armor stands before a burning pagoda"];
  const visualPresets = ["droplets cascading down deep cherry-red paint, windshield blurred with streaks of water, glowing headlights diffused through mist", "intricate black and blue patterns, predominantly yellow with symbols and doodles, mechanical elements on dark side", "colorful, futuristic design with vibrant glow, weathered metal textures and classic truck details"];
  const technicalPresets = ["cinematic lighting with glossy textures, shallow depth of field, moody composition", "macro photography detail, natural window lighting with soft shadows, high resolution", "monochromatic red palette, ambient lighting, steam and mist effects, wide-angle shot"];
  const atmosphericPresets = ["ambient red light enveloping the scene, steam rising, reflections dancing across wet surfaces", "van gogh style, vibrant digital illustration, warm and inviting mood with artistic flair", "mysterious and grim atmosphere, embers and ash swirling, dramatic contrast between light and shadow"];
  const buildPrompt = () => {
    let prompt = foundationOptions[foundationIndex];
    if (visualLayer) prompt += ", " + visualLayer;
    if (technicalLayer) prompt += ", " + technicalLayer;
    if (atmosphericLayer) prompt += ", " + atmosphericLayer;
    return prompt;
  };
  const copyToClipboard = () => {
    navigator.clipboard.writeText(buildPrompt());
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };
  const clearAll = () => {
    setVisualLayer("");
    setTechnicalLayer("");
    setAtmosphericLayer("");
  };
  return <div className="border rounded-lg p-6 space-y-6" style={{
    backgroundColor: '#f3f1e6',
    borderColor: '#556659'
  }}>
      <div className="text-center">
        <h3 className="text-lg font-semibold mb-2" style={{
    color: '#07130e'
  }}>Prompt Builder</h3>
        <p className="text-sm" style={{
    color: '#556659'
  }}>
          Build your prompt layer by layer following the enhancement hierarchy
        </p>
      </div>

      <div className="space-y-2">
        <label className="block text-sm font-medium">
          <span style={{
    color: '#556659'
  }}>Foundation Layer</span> (Required)
        </label>
        <div className="space-y-2">
          <div className="p-3 rounded border" style={{
    backgroundColor: '#dedbc9',
    borderColor: '#556659'
  }}>
            <code className="text-sm" style={{
    color: '#07130e'
  }}>{foundationOptions[foundationIndex]}</code>
          </div>
          <div className="flex flex-wrap gap-1">
            {foundationOptions.map((_, idx) => <button key={idx} onClick={() => setFoundationIndex(idx)} className={`text-xs px-2 py-1 rounded transition-colors hover:opacity-80 ${foundationIndex === idx ? 'font-semibold' : ''}`} style={{
    backgroundColor: foundationIndex === idx ? '#556659' : '#b9b5d0',
    color: foundationIndex === idx ? '#f3f1e6' : '#07130e'
  }}>
                {idx === 0 ? "Detective" : idx === 1 ? "Rainy Car" : idx === 2 ? "Robot Repair" : idx === 3 ? "Abstract Cat" : "Samurai"}
              </button>)}
          </div>
        </div>
      </div>

      <div className="space-y-2">
        <label className="block text-sm font-medium">
          <span style={{
    color: '#556659'
  }}>+ Visual Enhancement Layer</span>
        </label>
        <textarea value={visualLayer} onChange={e => setVisualLayer(e.target.value)} placeholder="Add visual details: appearance, setting, objects..." className="w-full p-3 border rounded resize-none focus:ring-2 focus:border-transparent" style={{
    borderColor: '#afaa92',
    backgroundColor: '#ffffff',
    color: '#07130e'
  }} rows="2" />
        <div className="flex flex-wrap gap-2">
          {visualPresets.map((preset, idx) => <button key={idx} onClick={() => setVisualLayer(preset)} className="text-xs px-2 py-1 rounded transition-colors hover:opacity-80" style={{
    backgroundColor: '#b9b5d0',
    color: '#07130e'
  }}>
              {idx === 0 ? "Wet Surfaces" : idx === 1 ? "Pattern Details" : "Futuristic Glow"}
            </button>)}
        </div>
      </div>

      <div className="space-y-2">
        <label className="block text-sm font-medium">
          <span style={{
    color: '#556659'
  }}>+ Technical Enhancement Layer</span>
        </label>
        <textarea value={technicalLayer} onChange={e => setTechnicalLayer(e.target.value)} placeholder="Add camera settings, lighting, style specifications..." className="w-full p-3 border rounded resize-none focus:ring-2 focus:border-transparent" style={{
    borderColor: '#afaa92',
    backgroundColor: '#ffffff',
    color: '#07130e'
  }} rows="2" />
        <div className="flex flex-wrap gap-2">
          {technicalPresets.map((preset, idx) => <button key={idx} onClick={() => setTechnicalLayer(preset)} className="text-xs px-2 py-1 rounded transition-colors hover:opacity-80" style={{
    backgroundColor: '#afaa92',
    color: '#07130e'
  }}>
              {idx === 0 ? "Cinematic" : idx === 1 ? "Natural Light" : "Moody Palette"}
            </button>)}
        </div>
      </div>

      <div className="space-y-2">
        <label className="block text-sm font-medium">
          <span style={{
    color: '#556659'
  }}>+ Atmospheric Enhancement Layer</span>
        </label>
        <textarea value={atmosphericLayer} onChange={e => setAtmosphericLayer(e.target.value)} placeholder="Add mood, emotion, narrative elements..." className="w-full p-3 border rounded resize-none focus:ring-2 focus:border-transparent" style={{
    borderColor: '#afaa92',
    backgroundColor: '#ffffff',
    color: '#07130e'
  }} rows="2" />
        <div className="flex flex-wrap gap-2">
          {atmosphericPresets.map((preset, idx) => <button key={idx} onClick={() => setAtmosphericLayer(preset)} className="text-xs px-2 py-1 rounded transition-colors hover:opacity-80" style={{
    backgroundColor: '#dedbc9',
    color: '#07130e'
  }}>
              {idx === 0 ? "Ambient Drama" : idx === 1 ? "Artistic Style" : "Dark Mystery"}
            </button>)}
        </div>
      </div>

      <div className="space-y-2">
        <label className="block text-sm font-medium">
          <span style={{
    color: '#07130e'
  }}>📝 Generated Prompt</span>
        </label>
        <div className="p-4 rounded border min-h-[80px]" style={{
    backgroundColor: '#dedbc9',
    borderColor: '#556659'
  }}>
          <code className="text-sm whitespace-pre-wrap" style={{
    color: '#07130e'
  }}>
            {buildPrompt()}
          </code>
        </div>
        <div className="flex gap-2 justify-end">
          <button onClick={clearAll} className="px-3 py-1 text-sm border rounded transition-colors hover:opacity-80" style={{
    color: '#556659',
    borderColor: '#afaa92',
    backgroundColor: 'transparent'
  }}>
            Clear All
          </button>
          <button onClick={copyToClipboard} className="px-3 py-1 text-sm rounded transition-colors hover:opacity-80" style={{
    backgroundColor: copied ? '#22c55e' : '#556659',
    color: '#f3f1e6'
  }}>
            {copied ? 'Copied!' : 'Copy Prompt'}
          </button>
        </div>
      </div>

      <div className="text-xs text-center pt-2 border-t" style={{
    color: '#afaa92',
    borderColor: '#afaa92'
  }}>
        💡 Try different combinations or write your own enhancements in each layer
      </div>
    </div>;
};

<Info>
  This interactive tool teaches you how to build effective FLUX prompts using the [enhancement layer approach](/guides/prompting_guide_t2i_essentials#the-enhancement-hierarchy). Start with a foundation and add layers to see how each enhancement affects your final prompt.
</Info>

## How to Use the Prompt Builder

The builder follows the proven **Foundation → Visual → Technical → Atmospheric** enhancement pattern:

<Steps>
  <Step title="Choose your foundation">
    Select one of the pre-built foundation prompts or understand how **Subject + Action + Style + Context** works
  </Step>

  <Step title="Add visual details">
    Enhance with specific visual elements: colors, objects, setting details, composition elements
  </Step>

  <Step title="Include technical specs">
    Add camera settings, lighting setup, artistic style specifications, and quality markers
  </Step>

  <Step title="Set the atmosphere">
    Define mood, emotional tone, and narrative elements that bring your vision to life
  </Step>
</Steps>

## Interactive Builder

<PromptBuilder />

## Next Steps

<CardGroup cols={2}>
  <Card title="Learn the Framework" href="/guides/prompting_guide_t2i_fundamentals">
    Understand the core principles behind effective prompt structure
  </Card>

  <Card title="Enhancement Techniques" href="/guides/prompting_guide_t2i_essentials">
    Master the layer-by-layer approach this builder teaches
  </Card>

  <Card title="Advanced Methods" href="/guides/prompting_guide_t2i_advanced">
    Explore professional photography and cinematic techniques
  </Card>

  <Card title="Start Creating" href="https://playground.bfl.ai">
    Take your built prompts to the FLUX Playground and generate images
  </Card>
</CardGroup>
