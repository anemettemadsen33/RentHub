# Page snapshot

```yaml
- generic [active]:
  - link "Skip to main content" [ref=e1] [cursor=pointer]:
    - /url: "#main-content"
  - generic [ref=e5]:
    - generic [ref=e6]:
      - text: We use cookies to measure performance (Web Vitals), analytics (usage & conversions) and optional marketing. Choose categories or accept all.
      - generic [ref=e7]:
        - generic [ref=e8]:
          - checkbox "Analytics" [checked] [ref=e9]
          - text: Analytics
        - generic [ref=e10]:
          - checkbox "Performance" [checked] [ref=e11]
          - text: Performance
        - generic [ref=e12]:
          - checkbox "Marketing (optional)" [ref=e13]
          - text: Marketing (optional)
    - generic [ref=e14]:
      - button "Decline" [ref=e15] [cursor=pointer]
      - button "Accept" [ref=e16] [cursor=pointer]
  - region "Notifications (F8)":
    - list
  - button "Open Next.js Dev Tools" [ref=e22] [cursor=pointer]:
    - img [ref=e23]
  - alert [ref=e26]
```