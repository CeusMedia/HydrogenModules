This module provides a help against Cross Site Request Forgery.

The aim is to protect POST requests of HTML forms against double use or calls from a third person.

Therefore you can use the helper to inject a token into form templates and check this token in controller action on form submit.

Tokens will be:

- stored in database after added to a form along with other information
- marked as outdated after a given time
- marked as replaced after a token for the same form has been injected
- marked as used after token check in controller action has taken place
- looked up in database on token check in controller action
- only successful if unused and matching with session and IP

Token data contains:

- token itself
- IP address on token generation
- session ID on token generation
- name of form where token has been used
- timestamp

**Attention: You will need to apply token generation and check for every form you want to secure.**
