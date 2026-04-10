# AI Assistant Support for Beartropy SAML2

Beartropy SAML2 includes AI assistant integration to help you configure SAML2 SSO.

## Supported AI Assistants

### Claude Code / Cursor / Other AI Tools
- Universal guide with API reference
- Cursor rules for configuration patterns
- Copy-paste ready examples

## Directory Structure

```
beartropy/saml2/
└── docs/
    ├── en/                        # User documentation (English)
    ├── es/                        # User documentation (Spanish)
    ├── llms/                      # LLM reference docs
    ├── components/                # API reference docs
    └── ai-assistants/
        ├── README.md              # This file
        ├── BEARTROPY_GUIDE.md     # Universal AI guide
        ├── cursor/
        │   └── .cursorrules       # Cursor configuration
        └── examples/
            └── saml2.md           # Integration examples
```

## Quick Start

### Using with Cursor

```bash
cp vendor/beartropy/saml2/docs/ai-assistants/cursor/.cursorrules .cursorrules
```

### Using with Other AI Tools

Point your AI assistant to:
```
vendor/beartropy/saml2/docs/ai-assistants/BEARTROPY_GUIDE.md
```

## License

MIT License.
