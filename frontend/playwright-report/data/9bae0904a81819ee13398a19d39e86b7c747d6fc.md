# Page snapshot

```yaml
- generic [active] [ref=e1]:
  - link "Skip to main content" [ref=e2] [cursor=pointer]:
    - /url: "#main-content"
  - generic [ref=e6]:
    - generic [ref=e7]:
      - heading "404" [level=1] [ref=e8]
      - heading "Page Not Found" [level=2] [ref=e9]
      - paragraph [ref=e10]: The page you're looking for doesn't exist or has been moved.
    - generic [ref=e11]:
      - link "Go Home" [ref=e12] [cursor=pointer]:
        - /url: /
        - button "Go Home" [ref=e13]:
          - img
          - text: Go Home
      - link "Browse Properties" [ref=e14] [cursor=pointer]:
        - /url: /properties
        - button "Browse Properties" [ref=e15]:
          - img
          - text: Browse Properties
  - region "Notifications (F8)":
    - list
```