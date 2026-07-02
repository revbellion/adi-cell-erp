# AI_RULES.md

# Universal AI Coding Rules

Version: 1.0

---

# PRIMARY GOAL

Always produce code that is:

- Correct
- Maintainable
- Readable
- Consistent
- Minimal
- Secure

Never optimize for cleverness over clarity.

---

# GENERAL PRINCIPLES

Always follow:

- KISS (Keep It Simple, Stupid)
- DRY (Don't Repeat Yourself)
- SOLID Principles
- Clean Code
- Separation of Concerns

Prefer consistency over personal preference.

---

# BEFORE WRITING CODE

Always:

1. Understand the problem.
2. Identify the root cause.
3. Understand the existing architecture.
4. Search for existing implementations.
5. Reuse existing code whenever possible.

Never write code before understanding the problem.

---

# PROBLEM SOLVING

When solving problems:

- Analyze first.
- Explain the root cause.
- Consider multiple solutions.
- Choose the simplest safe solution.

Never guess.

If information is missing,
ask for clarification.

---

# CODE MODIFICATION

Before modifying code:

- Identify affected files.
- Explain why changes are necessary.
- Minimize the scope of changes.

Never modify unrelated code.

---

# REUSE

Always prefer:

- Existing functions
- Existing services
- Existing utilities
- Existing components

Avoid duplicate logic.

---

# ARCHITECTURE

Respect the existing project architecture.

Never:

- Rewrite architecture
- Rename folders
- Move files
- Change design patterns

unless explicitly requested.

---

# CLEAN CODE

Write code that is:

- Self-explanatory
- Modular
- Easy to maintain
- Easy to test

Avoid unnecessary complexity.

---

# COMMENTS

Only write comments when they add value.

Do NOT explain obvious code.

Explain:

- business rules
- unusual logic
- complex algorithms

---

# ERROR HANDLING

Never ignore errors.

Handle exceptions properly.

Provide meaningful error messages.

Never swallow exceptions silently.

---

# SECURITY

Always:

- Validate inputs
- Sanitize data
- Escape output when necessary
- Respect authentication
- Respect authorization

Never:

- Hardcode secrets
- Hardcode passwords
- Hardcode API keys

---

# PERFORMANCE

Avoid:

- unnecessary loops
- repeated queries
- duplicated calculations
- unnecessary memory usage

Optimize only when necessary.

Readability comes first.

---

# DEPENDENCIES

Do not introduce new libraries unless:

- they solve a real problem
- existing tools cannot solve it

Prefer fewer dependencies.

---

# REFACTORING

Refactor only if it improves:

- readability
- maintainability
- simplicity

Avoid unnecessary refactoring.

---

# DATABASE

Never:

- delete data without confirmation
- modify schema without explanation
- perform dangerous operations silently

Always explain database changes.

---

# TESTING

Before finishing:

Check for:

- syntax errors
- logical errors
- edge cases
- null handling
- unused code
- dead code

---

# OUTPUT FORMAT

Always respond in this order:

1. Analysis
2. Root Cause
3. Proposed Solution
4. Files Affected
5. Risks
6. Implementation
7. Self Review

---

# SELF REVIEW

Before completing a task, verify:

- Is the solution correct?
- Is it the simplest solution?
- Does it introduce duplication?
- Does it follow the project style?
- Could it break existing functionality?

If uncertain, explain the uncertainty.

---

# IF INFORMATION IS MISSING

Never invent details.

Instead:

- explain what is missing
- explain why it is needed
- ask for the required information

---

# WHEN MULTIPLE SOLUTIONS EXIST

Present:

Option A
- Pros
- Cons

Option B
- Pros
- Cons

Recommend the best option with justification.

---

# COMMUNICATION

Be concise.

Avoid unnecessary explanations.

Focus on technical accuracy.

State assumptions clearly.

---

# DO NOT

Do NOT:

- Guess
- Hallucinate APIs
- Invent functions
- Invent file structures
- Rename code unnecessarily
- Rewrite unrelated code
- Add unnecessary complexity
- Break backward compatibility

---

# GOLDEN RULE

Every solution should satisfy:

✔ Correct

✔ Simple

✔ Maintainable

✔ Consistent

✔ Secure

✔ Minimal

✔ Reusable

If not, improve the solution before responding.