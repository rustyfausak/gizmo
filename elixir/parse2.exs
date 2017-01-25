defmodule NetCacheItem do
	defstruct [
		:class_id,
		:parent_cache_id,
		:branch_cache_id,
		:property_map,
		:class_name
	]
end

defmodule Parser do

	@moduledoc """

	"""

	def parse(path) do
		data = File.read! path
		{meta, _stream} = parse_meta(data)
		replay = Map.put(%Replay{}, :meta, meta)
		IO.inspect(replay, pretty: true)
	end

	def parse_meta(data) do
		{meta, data} = parse_header(data, %Meta{})
		parse_body(data, meta)
	end

	def parse_header(data, meta) do
		<<
			size1 :: little-unsigned-integer-size(32),
			crc1 :: little-unsigned-integer-size(32),
			version1 :: little-unsigned-integer-size(32),
			version2 :: little-unsigned-integer-size(32),
			data :: binary
		>> = data
		{label, data} = read_string(data)
		{properties, data} = read_map(data, &read_property/1)
		{Map.merge(meta, %Meta{
			size1: size1,
			crc1: crc1,
			version1: version1,
			version2: version2,
			label: label,
			properties: properties
		}), data}
	end

	def parse_body(data, meta) do
		<<
			size2 :: little-unsigned-integer-size(32),
			crc2 :: little-unsigned-integer-size(32),
			data :: binary
		>> = data
		{levels, data} = read_list(data, &read_string/1)
		{keyframes, data} = read_list(data, &read_keyframe/1)
		<< netstream_bytes :: little-unsigned-integer-size(32), data :: binary >> = data
		netstream_bits = netstream_bytes * 8
		<< netstream :: bits-size(netstream_bits), data :: binary >> = data
		{messages, data} = read_list(data, &read_message/1)
		{marks, data} = read_list(data, &read_mark/1)
		{packages, data} = read_list(data, &read_string/1)
		{objects, data} = read_list(data, &read_string/1)
		object_map = Enum.into(Enum.with_index(objects), %{}, fn({v, k}) -> {k, v} end)
		{names, data} = read_list(data, &read_string/1)
		{Map.merge(meta, %Meta{
			size2: size2,
			crc2: crc2,
			levels: levels,
			keyframes: keyframes,
			messages: messages,
			marks: marks,
			packages: packages,
			object_map: object_map,
			names: names
		}), netstream}
	end

	@doc
	def read_mark(data) do
		{type, data} = read_string(data)
		<< frame :: little-unsigned-integer-size(32), data :: binary >> = data
		mark = %Mark{
			type: type,
			frame: frame
		}
		{mark, data}
	end

	def read_message(data) do
		<< frame :: little-size(32), data :: binary >> = data
		{name, data} = read_string(data)
		{content, data} = read_string(data)
		message = %Message{
			frame: frame,
			name: name,
			content: content
		}
		{message, data}
	end

	def read_keyframe(data) do
		<< time :: little-float-size(32), data :: binary >> = data
		<< frame :: little-size(32), data :: binary >> = data
		<< position :: little-size(32), data :: binary >> = data
		keyframe = %Keyframe{
			time: time,
			frame: frame,
			position: position
		}
		{keyframe, data}
	end

	def read_property(data) do
		{type, data} = read_string(data)
		<< size :: little-size(64), data :: binary >> = data
		{value, data} = case to_string(type) do
			"ArrayProperty" ->
				{x, data} = read_list(data, fn x -> read_map(x, &read_property/1) end)
				{x, data}
				# read_list(data, &read_map(data, &read_property/1)/2)
			"BoolProperty" ->
				<< x :: little-size(8), data :: binary >> = data
				{if x == 1 do true else false end, data}
			"ByteProperty" ->
				{key, data} = read_string(data)
				{value, data} = read_string(data)
				{{key, value}, data}
			"FloatProperty" ->
				<< x :: little-float-size(32), data :: binary >> = data
				{x, data}
			"IntProperty" ->
				<< x :: little-size(32), data :: binary >> = data
				{x, data}
			"NameProperty" ->
				{x, data} = read_string(data)
				{x, data}
			"QWordProperty" ->
				<< x :: little-size(64), data :: binary >> = data
				{x, data}
			"StrProperty" ->
				{x, data} = read_string(data)
				{x, data}
			_ -> raise "unknown property type #{type}"
		end
		property = %Property{
			type: type,
			size: size,
			value: value
		}
		{property, data}
	end

	def read_list(data, read_element) do
		<< length :: little-size(32), data :: binary >> = data
		read_list(data, length, read_element)
	end

	def read_list(data, n, _read_element) when n < 1 do
		{[], data}
	end

	def read_list(data, n, read_element) do
		{element, data} = read_element.(data)
		{list, data} = read_list(data, n - 1, read_element)
		{[element | list] , data}
	end

	def read_map_entry(data, read_value) do
		{key, data} = read_string(data)
		if key == 'None' do
			{nil, nil, data}
		else
			{value, data} = read_value.(data)
			{key, value, data}
		end
	end

	def read_map(data, read_value) do
		{key, value, data} = read_map_entry(data, read_value)
		if key && value do
			{new_dictionary, data} = read_map(data, read_value)
			dictionary = Map.merge(
				%{key => value},
				new_dictionary
			)
			{dictionary, data}
		else
			{%{}, data}
		end
	end

	def read_string(data, string, n, _match) when n <= 1 do
		# Read the null terminator for the string
		<< _ :: size(8), data :: binary >> = data
		{string, data}
	end

	def read_string(data, string, n, match) do
		{char, data} = match.(data)
		read_string(data, string ++ [char], n - 1, match)
	end

	def read_string(<< length :: little-size(32), data :: binary >>) do
		if length > 0 do
			read_string(data, [], length, &read_utf8_char/1)
		else
			read_string(data, [], length * 2, &read_utf16_char/1)
		end
	end

	def read_utf8_char(data) do
		<< char :: utf8, data :: binary >> = data
		{char, data}
	end

	def read_utf16_char(data) do
		<< char :: utf16, data :: binary >> = data
		{char, data}
	end
end

Parser.parse hd(System.argv)
