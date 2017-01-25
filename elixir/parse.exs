defmodule Replay do
	defstruct [
		:size_of_properties,
		:size_of_remaining,
		:crc1,
		:crc2,
		:version1,
		:version2,
		:label,
		:properties,
		:levels,
		:keyframes,
		:messages,
		:marks,
		:packages,
		:object_map,
		:names,
		:class_map,
		:net_class_map
	]
end

defmodule Property do
	defstruct [
		:type,
		:size,
		:value
	]
end

defmodule Keyframe do
	defstruct [
		:time,
		:frame,
		:position
	]
end

defmodule Message do
	defstruct [
		:frame,
		:name,
		:content
	]
end

defmodule Mark do
	defstruct [
		:type,
		:frame
	]
end

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
	def parse(path) do
		replay = %Replay{}
		data = File.read! path

		{replay, _data} = parse_meta(data)
		IO.inspect replay.net_class_map
	end

	def parse_meta(data) do
		{replay, data} = parse_header(data)

		<< size_of_remaining :: little-size(32), data :: binary >> = data
		<< crc2 :: little-size(32), data :: binary >> = data

		{levels, data} = read_list(data, &read_string/1)
		{keyframes, data} = read_list(data, &read_keyframe/1)

		<< length_of_netstream :: little-size(32), data :: binary >> = data
		byte_length_of_netstream = length_of_netstream * 8
		<< netstream :: size(byte_length_of_netstream), data :: binary >> = data

		{messages, data} = read_list(data, &read_message/1)
		{marks, data} = read_list(data, &read_mark/1)
		{packages, data} = read_list(data, &read_string/1)

		{objects, data} = read_list(data, &read_string/1)
		object_map = Enum.into(Enum.with_index(objects), %{}, fn({v, k}) -> {k, v} end)

		{names, data} = read_list(data, &read_string/1)

		{class_map_items, data} = read_list(data, &read_class_map_item/1)
		class_map = Enum.into(class_map_items, %{})

		{net_cache, data} = read_list(data, fn(data) ->
			read_netcache_item(data, object_map)
		end)
		tmp_net_class_map = Enum.reduce(net_cache, %{},
			fn(net_cache_item, acc) ->
				Map.put(acc, net_cache_item.class_id, net_cache_item)
			end
		)
		sorted_keys = tmp_net_class_map |> Map.keys |> Enum.sort
		net_class_map = Enum.reduce(sorted_keys, %{}, fn(key, acc) ->
			tmp_net_cache_item = Map.fetch!(tmp_net_class_map, key)
		 	net_cache_item = %{tmp_net_cache_item |
		 		property_map: get_property_map(acc, tmp_net_cache_item)
		 	}
		 	Map.put(acc, key, net_cache_item)
		end)

		replay = %{replay |
			size_of_remaining: size_of_remaining,
			crc2: crc2,
			levels: levels,
			keyframes: keyframes,
			# netstream: netstream, # TODO
			messages: messages,
			marks: marks,
			packages: packages,
			object_map: object_map,
			names: names,
			class_map: class_map,
			net_class_map: net_class_map
		}

		{replay, data}
	end

	def get_property_map(net_class_map, net_cache_item) do
		# Map.merge(
		# 	net_cache_item.property_map,
		# 	parent
	end

	def find_in_cache(cache, cache_id) do
		{_, x} = Enum.find(cache, fn({_, x}) -> x.cache_id == cache_id end)
		x
	end

	def merge_net_cache_items(net_cache_item, net_cache_item2) do
		IO.puts("merging cache items #{net_cache_item.cache_id} and #{net_cache_item2.cache_id}")
		%{net_cache_item |
			property_map: Map.merge(
				net_cache_item.property_map,
				net_cache_item2.property_map
			)
		}
	end

	def parse_header(data) do
		<<
			size_of_properties :: little-size(32),
			crc1 :: little-size(32),
			version1 :: little-size(32),
			version2 :: little-size(32),
			data :: binary
		>> = data

		replay = %Replay{
			size_of_properties: size_of_properties,
			crc1: crc1,
			version1: version1,
			version2: version2
		}

		{label, data} = read_string(data)
		replay = %{replay | label: label}

		{properties, data} = read_map(data, &read_property/1)
		replay = %{replay | properties: properties}

		{replay, data}
	end

	def read_netcache_item(data, object_map) do
		<< class_id :: little-size(32), data :: binary >> = data
		<< parent_cache_id :: little-size(32), data :: binary >> = data
		<< branch_cache_id :: little-size(32), data :: binary >> = data
		{property_map_items, data} = read_list(data, &read_property_map_item/1)
		property_map = Enum.into(property_map_items, %{})
		net_cache_item = %NetCacheItem{
			class_id: class_id,
			parent_cache_id: parent_cache_id,
			branch_cache_id: branch_cache_id,
			property_map: property_map,
			class_name: Map.get(object_map, class_id)
		}
		{net_cache_item, data}
	end

	def read_property_map_item(data) do
		<< property_netstream_id :: little-size(32), data :: binary >> = data
		<< object_map_id :: little-size(32), data :: binary >> = data
		{{property_netstream_id, object_map_id}, data}
	end

	def read_class_map_item(data) do
		{name, data} = read_string(data)
		<< netstream_id :: little-size(32), data :: binary >> = data
		{{netstream_id, name}, data}
	end

	def read_mark(data) do
		{type, data} = read_string(data)
		<< frame :: little-size(32), data :: binary >> = data
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
