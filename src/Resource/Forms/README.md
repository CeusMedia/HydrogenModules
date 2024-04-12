# Forms Resource

...

## Process

...

- Forms
- Fills
- Double-Opt
- Customer Rules
- Manager Rules
- Transfers

## Form Construction

...

## Customer Rules

...

## Manager Rules

...

## Transfers

Received form data can be transferred to other targets. For example to an API of an online-served CRM tool.
You can add one or more transfers to every form.

### Target

A target is a data consuming service to which data can be sent using a local binding to this target.
So, for every target to be used an individual client implementation matching the consuming service interface needs to be installed.

Some targets may have a sandbox environment for testing next to the productional environment.
If the client implementation supports this, you may have several targets of one consuming service for selection.

Hist: Always avoid sending test data to the productional environment. Use the sandbox if available.

### DataMapper

Every implemented target will have its own storing data structure and therefore well defined data input fields.

To prepare received form data for transfers, transformation of field names and their values is needed.
This is done by a data mapping logic which needs to be configures by defining rules for every transfer.

A set of transfer rules can consist of these rule types:

- copy: carry form fields from input to output without any changes
- map: carry values from input to output data having different field names
- set: ...
- create: ...
- filter: ...
- db: resolve values using the database

### Data Streams

It is important to understand, that there is input data which coming from the form, and there is output data which shall be sent to the target.
The data mapper will carry values from input to output and can modify field names and values according to the rules.

#### Rules

##### copy

````
	"copy": [
		"field1",
		"field2"
	]
````
This will copy input data directly to the output. Field names are matching and values to not need to be changed.

##### map
This will copy values from input to output while changing the field names.
````
	"map": {
		"form_field_1": "target_field_a",
		"form_field_2": "target_field_b"
	}
````
Use this map to carry additionally collected values, created by one of the following rules.

##### set
This will store two more fields into output data.
````
	"set": {
		"target_field_c": "@form_field_15",
		"target_field_d": "!date"
	}
````
The first field will have the value of the input field 'form_field_15'.
The second field will have a generated value using the date function.

##### translate
This will map field values from local to target domain.
````
	"translate": {
		"form_field_2": {
			"formValue1": "targetValueA",
			"formValue2": "targetValueB"
		}
	}
````

**Example:**
Lets say a gender, in the local domain: 0-unkown, 1-without, 2-inter, 3-female, 4-male
The targets has this outdated domain data: 0-female, 1-male, 2-other
````
	"translate": {
		"form_gender": {
			"0": "2",
			"1": "2",
			"3": "0",
			"4": "1"
		}
	}
````
So, form values 0, 1 and 2 will become 2-other and the binary sex values are mapped from 3 and 4 to 0 and 1.

Hint: The translate will be done in the input data before the mapping or copying will happen.
It is not important, where the translations are defined.


##### filter

...


##### create
````
	"create": {
		"target_field_e": {
			"lines": [
				"Hello World!",
				"This is [@form_field_nickname] speaking.",
				"The current datetime is [!datetime]."
			]
		}
	}

````
Here, a new field in the output data will be created by the name 'target_field_e'.
Its value can be defined by one or more lines containing:

- static text
- input data field references
- function references

Both references need to be sorrounded by [...].

Attention: Since the created value will be stored into output data, this additional field cannot be taken into account on other rules.


##### db
````
	"db": {
		"my_input_field": {
			"table": "my_table",
			"column": "my_target_field_value",
			"index": {
				"my_input_field_value": "@input_field"
			},
			"to": "request",
			"onEmpty": 0
		}
	}
````


The form has a field called input_field, which holds a value of the local applications data domain.

In this example, we use the current database connection to:
- look into table 'my_table'
- try to get the value of column 'my_target_field_value'
- by search for matching rows by indexing on 'my_input_field_value'
- and accept row if index column has the value of the form data input field input_field

The found column value will be stored directly into the output data, unless you set to store it to 'input' or 'request' (legacy).
Hint: Values stored in output cannot be addressed on further rules, so use 'to' with 'input' if you create intermediate fields.

Attention: If no row could be found, the mapper will throw an exception, unless you set a fallback value with 'onEmpty'.

